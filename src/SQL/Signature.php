<?php

namespace Aaronidas\SQLLexer\SQL;

final class Signature
{
    private $tokenizer;

    public function __construct($sql)
    {
        $this->tokenizer = new Tokenizer($sql);
    }

    public function parse()
    {
        $tokens = $this->tokenizer->scan();
        $firstElement = $tokens->nextValue();

        $result = '';

        if (null === $firstElement) {
            return $result;
        }

        switch ($firstElement->type()) {
            case TokenEnum::T_SELECT:
                $result = $this->parseSelect($tokens);
                break;
        }

        return $result;
    }

    /**
     * @return string
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
                    $nextIdent = $tokens->until(TokenEnum::T_IDENT);
                    $table = $this->parseDottedIdent($nextIdent, $tokens);
                    return \sprintf('SELECT FROM %s', $table);
            }
        }

        return 'SELECT FROM Unknown';
    }

    /**
     * @var Token|null $currentTokenCollection
     * @return string
     */
    private function parseDottedIdent($currentTokenCollection, TokenCollection $tokenCollection)
    {
        if (null === $currentTokenCollection) {
            return 'Unknown';
        }

        $table = $currentTokenCollection->content();

        $previous = $currentTokenCollection;

        while ($current = $tokenCollection->nextValue()) {
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
}
