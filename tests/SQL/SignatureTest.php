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
            [
                'SELECT * FROM foo-- abc
./*def*/bar',
                'SELECT FROM foo.bar'
            ],
            [
                'SELECT *,(SELECT COUNT(*) FROM table2 WHERE table2.field1 = table1.id) AS count FROM table1 WHERE table1.field1 = \'value\'',
                'SELECT FROM table1'
            ],
            [
                'SELECT * FROM (SELECT foo FROM bar) AS foo_bar',
                'SELECT'
            ],
            [
                'DELETE FROM foo.bar WHERE baz=1',
                'DELETE FROM foo.bar'
            ],
        ];
    }
}
