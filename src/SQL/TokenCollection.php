<?php

namespace Aaronidas\SQLLexer\SQL;

final class TokenCollection
{
    private $tokens;
    private $position;

    /**
     * @var Token[] $tokens
     */
    public function __construct(array $tokens)
    {
        $this->tokens = $tokens;
        $this->position = 0;
    }

    /**
     * @return Token
     */
    public function nextValue()
    {
        $value = null;
        if (true === \array_key_exists($this->position, $this->tokens)) {
            $value = $this->tokens[$this->position];
        }

        $this->position++;

        return $value;
    }

    /**
     * @return Token|null
     */
    public function until($tokenEnum)
    {
        while ($element = $this->nextValue()) {
            if ($tokenEnum === $element->type()) {
                return $element;
            }
        }

        return null;
    }

    /**
     * @return Token|null
     */
    public function current()
    {
        $value = null;
        if (true === \array_key_exists($this->position, $this->tokens)) {
            $value = $this->tokens[$this->position];
        }

        return $value;
    }

    /**
     * @param int $length
     * @return Token|null
     */
    public function peek($length)
    {
        $value = null;
        if (true === \array_key_exists($this->position + $length, $this->tokens)) {
            $value = $this->tokens[$this->position + $length];
        }

        return $value;
    }
}
