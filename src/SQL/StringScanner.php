<?php

namespace Aaronidas\SQLLexer\SQL;

final class StringScanner
{
    /**
     * @var string
     */
    private $string;

    /**
     * @var int
     */
    private $length;

    /**
     * @var int
     */
    private $currentPos;

    /**
     * @var string $string
     */
    public function __construct($string)
    {
        $this->string = $string;
        $this->length = \strlen($string);
        $this->currentPos = 0;
    }

    /**
     * @return int
     */
    public function currentPosition()
    {
        return $this->currentPos;
    }

    /**
     * @var string $pattern
     */
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

    /**
     * @return string|null
     */
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

    /**
     * @return bool
     */
    private function isEos()
    {
        return $this->currentPos >= $this->length;
    }

    /**
     * @return string|null
     */
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
