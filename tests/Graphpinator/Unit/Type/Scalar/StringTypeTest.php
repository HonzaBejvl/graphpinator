<?php

declare(strict_types=1);

namespace Infinityloop\Tests\Graphpinator\Unit\Type\Scalar;

final class StringTypeTest extends \PHPUnit\Framework\TestCase
{
    public function simpleDataProvider() : array
    {
        return [
            ['abc'],
            ['123'],
            [null],
        ];
    }

    public function invalidDataProvider() : array
    {
        return [
            [123],
            [123.123],
            [true],
            [[]],
        ];
    }

    /**
     * @dataProvider simpleDataProvider
     */
    public function testValidateValue($rawValue): void
    {
        $string = new \Infinityloop\Graphpinator\Type\Scalar\StringType();
        $string->validateValue($rawValue);

        self::assertSame($string->getName(), 'String');
    }

    /**
     * @dataProvider invalidDataProvider
     */
    public function testValidateValueInvalid($rawValue): void
    {
        $this->expectException(\Exception::class);

        $string = new \Infinityloop\Graphpinator\Type\Scalar\StringType();
        $string->validateValue($rawValue);
    }
}
