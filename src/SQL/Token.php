<?php

namespace Aaronidas\SQLLexer\SQL;

final class Token
{
    private $type;
    private $content;

    public function __construct($type, $content)
    {
        $this->type = $type;
        $this->content = $content;
    }

    public function type()
    {
        return $this->type;
    }

    public function content()
    {
        return $this->content;
    }
}
