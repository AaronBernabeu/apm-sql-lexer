<?php

namespace SQL;

use Aaronidas\SQLLexer\SQL\Tokenizer;
use PHPUnit\Framework\TestCase;

final class TokenizerTest extends TestCase
{
    /** @test */
    public function tokenizerTest()
    {
        $tokenizer = new Tokenizer('SELECT * FROM foo.bar');
        $tokenizer->scan();
    }
}
