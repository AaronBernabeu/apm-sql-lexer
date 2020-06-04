# SQL Lexer for APM agents

Simple and lightweight parser for span names in APM transactions written in PHP.

## Installation

1) Install via [composer]

    ```shell script
    composer require aaronidas/apm-sql-lexer
    ```

## How to use

    ```php
    Signature::parse($query);
    ```
Example:

    ```php
    $spanName = Signature::parse('SELECT * FROM foo');
    var_dump($spanName);
    // output: SELECT FROM foo
    ```

##### Reference doc for development:
    [Reference doc]: https://docs.google.com/document/d/1sblkAP1NHqk4MtloUta7tXjDuI_l64sT2ZQ_UFHuytA/edit#heading=h.549ltma0zvhu

* PostgreSQL dollar quoting not implemented yet
