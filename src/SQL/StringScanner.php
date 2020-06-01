<?php

namespace Aaronidas\SQLLexer\SQL;

final class StringScanner
{
    private $string;
    private $length;
    private $currentPos;

    public function __construct($string)
    {
        $this->string = $string;
        $this->length = \strlen($string);
        $this->currentPos = 0;
    }

    public function currentPosition()
    {
        return $this->currentPos;
    }

    public function skip($pattern)
    {
        if ($this->isEos()) {
            return;
        }
        if(1 === \preg_match($pattern, $this->peek(1))) {
            ++$this->currentPos;
            $this->skip($pattern);
        }
    }

    public function getCurrentChar()
    {
        if ($this->isEos()) {
            return null;
        }

        $length = 1;
        if ($this->currentPos + $length > $this->length) {
            $length = $this->length - $this->currentPos;
        }

        $match = \mb_substr($this->string, $this->currentPos, $length);
        $this->currentPos += $length;

        return $match;
    }

    private function isEos()
    {
        return $this->currentPos >= $this->length;
    }

    public function peek($length)
    {
        if ($this->isEos()) {
            return null;
        }

        if ($this->currentPos + $length > $this->length) {
            $length = $this->length - $this->currentPos;
        }
        return substr($this->string, $this->currentPos, $length);
    }
}
