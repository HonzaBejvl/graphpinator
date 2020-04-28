<?php

declare(strict_types=1);

namespace Graphpinator\Tests\Unit\Value;

final class InputValueTest extends \PHPUnit\Framework\TestCase
{
    public function testApplyDefaults() : void
    {
        $fields = [];
        $defaults = ['field' => \Graphpinator\Resolver\Value\LeafValue::create('random', \Graphpinator\Type\Container\Container::String())];

        $type = $this->createMock(\Graphpinator\Type\InputType::class);
        $type->expects($this->exactly(2))->method('getArguments')->willReturn(
            new \Graphpinator\Argument\ArgumentSet([new \Graphpinator\Argument\Argument('field', \Graphpinator\Type\Container\Container::String())])
        );
        $type->expects($this->once())->method('isInputable')->willReturn(true);
        $type->expects($this->once())->method('applyDefaults')->with($fields)->willReturn($defaults);

        $value = \Graphpinator\Resolver\Value\InputValue::create($fields, $type);
        self::assertTrue(isset($value['field']));
        self::assertFalse(isset($value['field0']));
        self::assertSame($defaults['field'], $value['field']);
    }

    public function testProvidedValue() : void
    {
        $fields = ['field' => 'random'];

        $type = $this->createMock(\Graphpinator\Type\InputType::class);
        $type->expects($this->exactly(2))->method('getArguments')->willReturn(
            new \Graphpinator\Argument\ArgumentSet([new \Graphpinator\Argument\Argument('field', \Graphpinator\Type\Container\Container::String())])
        );
        $type->expects($this->once())->method('isInputable')->willReturn(true);
        $type->expects($this->once())->method('applyDefaults')->with($fields)->willReturn($fields);

        $value = \Graphpinator\Resolver\Value\InputValue::create($fields, $type);
        self::assertSame($fields, $value->getRawValue());
    }

    public function testNull() : void
    {
        $type = $this->createMock(\Graphpinator\Type\InputType::class);

        self::assertInstanceOf(\Graphpinator\Resolver\Value\NullValue::class, \Graphpinator\Resolver\Value\InputValue::create(null, $type));
    }

    public function testInvalidField() : void
    {
        $this->expectException(\Exception::class);

        $fields = ['field0' => 123];

        $type = $this->createMock(\Graphpinator\Type\InputType::class);
        $type->expects($this->exactly(2))->method('getArguments')->willReturn(new \Graphpinator\Argument\ArgumentSet([]));
        $type->expects($this->once())->method('isInputable')->willReturn(true);
        $type->expects($this->once())->method('applyDefaults')->with($fields)->willReturn($fields);

        $value = \Graphpinator\Resolver\Value\InputValue::create($fields, $type);
    }
}
