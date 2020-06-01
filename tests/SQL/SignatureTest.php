<?php

namespace Aaronidas\SQLLexer\Tests\SQL;

use Aaronidas\SQLLexer\SQL\Signature;
use Aaronidas\SQLLexer\SQL\Tokenizer;
use PHPUnit\Framework\TestCase;

class SignatureTest extends TestCase
{
    /** @test */
    public function signatureTest()
    {
        $signature = new Signature('SELECT * FROM foo.bar');
        $result = $signature->parse();

        $this->assertEquals('SELECT FROM foo.bar', $result);
    }
}
