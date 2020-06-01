<?php

namespace Aaronidas\SQLLexer\Tests\SQL;

use Aaronidas\SQLLexer\SQL\Signature;
use PHPUnit\Framework\TestCase;

final class SignatureTest extends TestCase
{
    /**
     * @test
     * @dataProvider sqlProvider
     */
    public function signature_test($query, $expected)
    {
        $result = (new Signature($query))->parse();

        $this->assertEquals($expected, $result);
    }

    public function sqlProvider()
    {
        return [
            [
                'SELECT * FROM foo',
                'SELECT FROM foo'
            ],
            [
                'SELECT * FROM foo.bar',
                'SELECT FROM foo.bar'
            ],
            [
                '',
                ''
            ],
            [
                ' ',
                ''
            ],
            [
                'SELECT * FROM `foo.bar`',
                'SELECT FROM foo.bar'
            ],
            [
                'SELECT * FROM "foo.bar"',
                'SELECT FROM foo.bar'
            ],
            [
                'SELECT * FROM [foo.bar]',
                'SELECT FROM foo.bar'
            ],
            [
                'SELECT (x, y) FROM foo,bar,baz',
                'SELECT FROM foo'
            ],
            [
                'SELECT * FROM foo JOIN bar',
                'SELECT FROM foo'
            ],
            [
                'SELECT * FROM dollar$bill',
                'SELECT FROM dollar$bill'
            ],
            [
                'SELECT id FROM "myta\n-æøåble" WHERE id = 2323',
                'SELECT FROM myta\n-æøåble'
            ],
            //[
            //    'SELECT * FROM foo-- abc\n./*def*/bar',
            //    'SELECT FROM foo.bar'
            //],
        ];
    }
}
