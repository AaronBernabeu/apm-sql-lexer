<?php

namespace Aaronidas\SQLLexer\SQL;

final class Tokenizer
{
    /**
     * @var string
     */
    private $input;

    /**
     * @var StringScanner
     */
    private $scanner;

    /**
     * @var int
     */
    private $currentStartPosition;

    const SPACE = '/[[:space:]]+/';
    const ALPHA = '/[[:alpha:]]/';
    const DIGIT = '/\d/';

    /**
     * @var string $input
     */
    public function __construct($input)
    {
        $this->input = $input;
        $this->scanner = new StringScanner($input);
        $this->currentStartPosition = $this->scanner->currentPosition();
    }

    /**
     * @return TokenCollection
     */
    public function scan()
    {
        $tokens = [];
        while (true) {
            $this->scanner->skip(self::SPACE);
            $this->currentStartPosition = $this->scanner->currentPosition();
            $char = $this->scanner->getCurrentChar();
            if (null === $char) {
                break;
            }
            $tokens[] = $this->nextToken($char);
        }

        return new TokenCollection($tokens);
    }

    /**
     * @var string $char
     * @return Token|null
     */
    private function nextToken($char)
    {
        $token = null;
        switch ($char) {
            case '_':
                $token = $this->keywordOrIdent(false);
                break;
            case '.':
                $token = new Token(TokenEnum::T_PERIOD, $char);
                break;
            case '`':
                $token = $this->quotedIdent('`');
                break;
            case '"':
                $token = $this->quotedIdent('"');
                break;
            case '[':
                $token = $this->quotedIdent(']');
                break;
            case '(':
                $token = new Token(TokenEnum::T_LPAREN, $char);
                break;
            case ')':
                $token = new Token(TokenEnum::T_RPAREN, $char);
                break;
            case '/':
                $token = $this->bracketedComment();
                break;
            case '-':
                $token = $this->simpleComment();
                break;
            case "'":
                $token = $this->stringLiteral();
                break;
            case (bool)preg_match(self::ALPHA, $char):
                $token = $this->keywordOrIdent(true);
                break;
            default:
                $token = new Token(TokenEnum::T_OTHER, $char);
        }

        return $token;
    }

    /**
     * @return Token|null
     */
    private function stringLiteral()
    {
        $delimiter = "'";

        while (null !== $char = $this->scanner->getCurrentChar()) {
            if ("\\" === $char) {
                // Skip escaped character: 'Aaron\'s house'
                $this->getCurrentText();
                continue;
            }

            if ($char !== $delimiter) {
                continue;
            }

            if (null === $this->scanner->peek(1) || $this->scanner->peek(1) !== $delimiter) {
                return new Token(TokenEnum::T_STRING, $this->getCurrentText());
            }

            $this->scanner->getCurrentChar();
        }

        return null;
    }

    /**
     * @return Token|null
     */
    private function bracketedComment()
    {
        if ('*' !== $this->scanner->peek(1)) {
            return new Token(TokenEnum::T_OTHER, $this->getCurrentText());
        }

        $nesting = 1;

        while (null !== $char = $this->scanner->getCurrentChar()) {
            switch ($char) {
                case '/':
                    if ('*' !== $this->scanner->peek(1)) {
                        continue 2;
                    }
                    $this->scanner->getCurrentChar();
                    $nesting++;
                    break;
                case '*':
                    if ('/' !== $this->scanner->peek(1)) {
                        continue 2;
                    }
                    $this->scanner->getCurrentChar();
                    $nesting--;
                    if (0 === $nesting) {
                        return new Token(TokenEnum::T_COMMENT, $this->getCurrentText());
                    }
            }
        }

        return null;
    }

    /**
     * @return Token
     */
    private function simpleComment()
    {
        if ('-' !== $this->scanner->peek(1)) {
            return new Token(TokenEnum::T_OTHER, $this->getCurrentText());
        }

        while (null !== $char = $this->scanner->getCurrentChar()) {
            if ("\n" === $char) {
                break;
            }
        }

        return new Token(TokenEnum::T_COMMENT, $this->getCurrentText());
    }

    /**
     * @var string char
     * @return Token
     */
    private function quotedIdent($delimiter)
    {
        while (null !== $char = $this->scanner->getCurrentChar()) {
            if ($char !== $delimiter) {
                continue;
            }

            if ('"' === $delimiter && $this->scanner->peek(1) === $delimiter) {
                continue;
            }

            break;
        }

        return new Token(TokenEnum::T_IDENT, $this->getQuotedCurrentText());
    }

    /**
     * @var bool $possibleKeyword
     * @return Token
     */
    private function keywordOrIdent($possibleKeyword)
    {
        while (null !== $char = $this->scanner->peek(1)) {
            if ('_' === $char || '$' === $char || 1 === \preg_match(self::DIGIT, $char)) {
                $possibleKeyword = false;
                $this->scanner->getCurrentChar();
                continue;
            }

            if (1 === \preg_match(self::ALPHA, $char)) {
                $this->scanner->getCurrentChar();
                continue;
            }

            break;
        }

        if (false === $possibleKeyword) {
            return new Token(TokenEnum::T_IDENT, $this->getCurrentText());
        }

        return new Token(TokenEnum::findKeyword($this->getCurrentText()), $this->getCurrentText());
    }

    /**
     * @return string
     */
    private function getCurrentText()
    {
        return \substr($this->input, $this->currentStartPosition, $this->scanner->currentPosition() - $this->currentStartPosition);
    }

    /**
     * @return string
     */
    private function getQuotedCurrentText()
    {
        return \substr($this->input, $this->currentStartPosition + 1, ($this->scanner->currentPosition() - ($this->currentStartPosition + 1)) - 1);
    }
}
