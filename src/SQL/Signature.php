<?php

namespace Aaronidas\SQLLexer\SQL;

final class Signature
{
    /**
     * @var Tokenizer
     */
    private $tokenizer;

    /**
     * @var string
     */
    private $sql;

    /**
     * @var string $sql
     */
    public function __construct($sql)
    {
        $this->sql = $sql;
        $this->tokenizer = new Tokenizer($sql);
    }

    /**
     * @return string
     */
    public function parse()
    {
        $tokens = $this->tokenizer->scan();
        $firstElement = $tokens->nextValue();

        $result = null;

        if (null === $firstElement) {
            return $this->parseFallback();
        }

        switch ($firstElement->type()) {
            case TokenEnum::T_SELECT:
                $result = $this->parseSelect($tokens);
                break;
            case TokenEnum::T_DELETE:
                $result = $this->parseDelete($tokens);
                break;
            case TokenEnum::T_UPDATE:
                $result = $this->parseUpdate($tokens);
                break;
            case TokenEnum::T_INSERT:
            case TokenEnum::T_REPLACE:
                $result = $this->parseInsertOrReplace($firstElement, $tokens);
                break;
            case TokenEnum::T_CALL:
                $result = $this->parseCall($tokens);
                break;
        }

        if (null === $result) {
            $result = $this->parseFallback();
        }

        return $result;
    }

    /**
     * @return string
     */
    private function parseFallback()
    {
        $parts = explode(' ', $this->sql);
        $result = '';
        if (true === \array_key_exists(0, $parts)) {
            $result = $parts[0];
        }

        return ltrim($result);
    }

    /**
     * @return string|null
     */
    private function parseSelect(TokenCollection $tokens)
    {
        $level = 0;
        while ($token = $tokens->nextValue()) {
            switch ($token->type()) {
                case TokenEnum::T_LPAREN:
                    $level++;
                    break;
                case TokenEnum::T_RPAREN:
                    $level--;
                    break;
                case TokenEnum::T_FROM:
                    if ($level > 0) {
                        continue 2;
                    }
                    if (false === $this->isNextToken(TokenEnum::T_IDENT, $tokens)) {
                        break 2;
                    }
                    $nextIdent = $tokens->until(TokenEnum::T_IDENT);
                    $table = $this->parseDottedIdent($nextIdent, $tokens);
                    $return = null;
                    if (null !== $table) {
                        $return = \sprintf('SELECT FROM %s', $table);
                    }
                    return $return;
            }
        }

        return null;
    }

    /**
     * @var Token|null $currentTokenCollection
     * @return string
     */
    private function parseDottedIdent($currentTokenCollection, TokenCollection $tokenCollection)
    {
        if (null === $currentTokenCollection) {
            return null;
        }

        $table = $currentTokenCollection->content();

        $previous = $currentTokenCollection;

        while ($current = $tokenCollection->nextValue()) {
            if (TokenEnum::T_COMMENT === $current->type()) {
                continue;
            }
            if (TokenEnum::T_IDENT === $previous->type() && TokenEnum::T_PERIOD === $current->type()) {
                $table .= $current->content();
                $previous = $current;
                continue;
            }
            if (TokenEnum::T_PERIOD === $previous->type() && TokenEnum::T_IDENT === $current->type()) {
                $table .= $current->content();
                $previous = $current;
                continue;
            }

            break;
        }

        return $table;
    }

    /**
     * @return bool
     */
    public function isNextToken($token, TokenCollection $tokenCollection)
    {
        $peekLength = 0;
        $nextToken = null;
        while ($nextToken = $tokenCollection->peek($peekLength)) {
            if ($nextToken !== TokenEnum::T_COMMENT) {
                break;
            }
            $peekLength++;
        }

        return null !== $nextToken && $nextToken->type() === $token;
    }

    /**
     * @return string|null
     */
    private function parseDelete(TokenCollection $tokens)
    {
        $nextFrom = $tokens->until(TokenEnum::T_FROM);
        if (null === $nextFrom || false === $this->isNextToken(TokenEnum::T_IDENT, $tokens)) {
            return null;
        }

        $nextIdent = $tokens->until(TokenEnum::T_IDENT);
        $table = $this->parseDottedIdent($nextIdent, $tokens);

        return \sprintf('DELETE FROM %s', $table);
    }

    /**
     * @return string|null
     */
    private function parseUpdate(TokenCollection $tokens)
    {
        if (false === $this->isNextToken(TokenEnum::T_IDENT, $tokens)) {
            return 'UPDATE';
        }

        $table = $tokens->until(TokenEnum::T_IDENT)->content();

        $period = false;
        $firstPeriod = false;

        while ($token = $tokens->nextValue()) {
            switch ($token->type()) {
                case TokenEnum::T_IDENT:
                    if (true === $period) {
                        $table .= $token->content();
                        $period = false;
                    }

                    if (false === $firstPeriod) {
                        $table = $token->content();
                    }
                    break;
                case TokenEnum::T_PERIOD:
                    $period = true;
                    $firstPeriod = true;
                    $table .= $token->content();
                    break;
                default:
                    return \sprintf('UPDATE %s', $table);
            }
        }

        return null;
    }

    /**
     * @return string|null
     */
    private function parseInsertOrReplace(Token $action, TokenCollection $tokens)
    {
        $nextInto = $tokens->until(TokenEnum::T_INTO);

        if (null === $nextInto
            || false === $this->isNextToken(TokenEnum::T_IDENT, $tokens)) {
            return null;
        }

        $nextIdent = $tokens->until(TokenEnum::T_IDENT);
        $table = $this->parseDottedIdent($nextIdent, $tokens);

        return \sprintf('%s INTO %s', $action->content(), $table);
    }

    /**
     * @return string|null
     */
    private function parseCall(TokenCollection $tokens)
    {
        $nextIdent = $tokens->until(TokenEnum::T_IDENT);
        if (null === $nextIdent) {
            return null;
        }

        return \sprintf('CALL %s', $nextIdent->content());
    }
}
