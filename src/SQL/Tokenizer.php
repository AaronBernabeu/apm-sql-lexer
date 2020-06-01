<?php

namespace Aaronidas\SQLLexer\SQL;

final class Tokenizer
{
    private $input;
    private $scanner;
    private $currentStartPosition;

    const SPACE = '/[[:space:]]+/';
    const ALPHA = '/[[:alpha:]]/';
    const DIGIT = '/\d/';

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

    private function nextToken($char)
    {
        $token = null;
        switch ($char) {
            case '_':
                dump('_');
                break;
            case '.':
                $token = new Token(TokenEnum::T_PERIOD, $char);
                break;
            case '$':
                dump('$');
                break;
            case '`':
                dump('`');
                break;
            case '"':
                dump('"');
                break;
            case '[':
                dump('[');
                break;
            case '(':
                dump('(');
                break;
            case ')':
                dump(')');
                break;
            case '/':
                dump('/');
                break;
            case '-':
                dump('-');
                break;
            case "'":
                dump("'");
                break;
            case (bool)preg_match(self::ALPHA, $char):
                $token = $this->keywordOrIdent(true);
                break;
            default:
                $token = new Token(TokenEnum::T_OTHER, $char);
        }

        return $token;
    }

    private function keywordOrIdent($possibleKeyword)
    {
        while ($char = $this->scanner->peek(1)) {
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
            return TokenEnum::T_IDENT;
        }

        return new Token(TokenEnum::findKeyword($this->getCurrentText()), $this->getCurrentText());
    }

    private function getCurrentText()
    {
        return \substr($this->input, $this->currentStartPosition, $this->scanner->currentPosition() - $this->currentStartPosition);
    }
}
