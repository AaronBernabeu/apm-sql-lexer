<?php

namespace Aaronidas\SQLLexer\SQL;

final class TokenEnum
{
    const T_OTHER = ':OTHER';
    const T_COMMENT = ':COMMENT';
    const T_IDENT = ':IDENT';
    const T_NUMBER = ':NUMBER';
    const T_STRING = ':STRING';

    const T_PERIOD = ':PERIOD';
    const T_COMMA = ':COMMA';
    const T_LPAREN = ':LPAREN';
    const T_RPAREN = ':RPAREN';

    const T_AS = ':AS';
    const T_CALL = ':CALL';
    const T_DELETE = ':DELETE';
    const T_FROM = ':FROM';
    const T_INSERT = ':INSERT';
    const T_INTO = ':INTO';
    const T_OR = ':OR';
    const T_REPLACE = ':REPLACE';
    const T_SELECT = ':SELECT';
    const T_SET = ':SET';
    const T_TABLE = ':TABLE';
    const T_TRUNCATE = ':TRUNCATE';
    const T_UPDATE = ':UPDATE';

    /**
     * @return int
     */
    public static function minKeywordLength()
    {
        return \array_keys(self::keywords())[0];
    }

    /**
     * @return int
     */
    public static function maxKeywordLength()
    {
        return \array_keys(self::keywords())[\count(self::keywords()) - 1];
    }

    /**
     * @return array
     */
    public static function keywords()
    {
        return [
            2 => [self::T_AS, self::T_OR],
            3 => [self::T_SET],
            4 => [self::T_CALL, self::T_FROM, self::T_INTO],
            5 => [self::T_TABLE],
            6 => [self::T_DELETE, self::T_INSERT, self::T_SELECT, self::T_UPDATE],
            7 => [self::T_REPLACE],
            8 => [self::T_TRUNCATE]
        ];
    }

    /**
     * @return string
     */
    public static function findKeyword($currentText)
    {
        $textLength = \strlen($currentText);
        if ($textLength < self::minKeywordLength()
            || $textLength > self::maxKeywordLength()) {
            return self::T_IDENT;
        }

        foreach (self::keywords()[$textLength] as $theKeyword) {
            if ((':'.strtoupper($currentText)) === $theKeyword) {
                return $theKeyword;
            }
        }

        return self::T_IDENT;
    }
}
