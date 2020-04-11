<?php

declare(strict_types = 1);

namespace Graphpinator\Tests\Spec;

final class TestSchema
{
    use \Nette\StaticClass;

    public static function getSchema() : \Graphpinator\Type\Schema
    {
        return new \Graphpinator\Type\Schema(
            self::getTypeResolver(),
            self::getQuery(),
        );
    }

    public static function getTypeResolver() : \Graphpinator\Type\Container\Container
    {
        return new class extends \Graphpinator\Type\Container\Container
        {
            public function getType(string $name): \Graphpinator\Type\Contract\NamedDefinition
            {
                return $this->getAllTypes()[$name];
            }

            public function getAllTypes() : array
            {
                return [
                    'Query' => TestSchema::getQuery(),
                    'Abc' => TestSchema::getTypeAbc(),
                    'Xyz' => TestSchema::getTypeXyz(),
                    'Zzz' => TestSchema::getTypeZzz(),
                    'TestInterface' => TestSchema::getInterface(),
                    'TestUnion' => TestSchema::getUnion(),
                    'TestInput' => TestSchema::getInput(),
                    'TestInnerInput' => TestSchema::getInnerInput(),
                    'TestEnum' => TestSchema::getEnum(),
                    'Int' => \Graphpinator\Type\Container\Container::Int(),
                    'Float' => \Graphpinator\Type\Container\Container::Float(),
                    'String' => \Graphpinator\Type\Container\Container::String(),
                    'Boolean' => \Graphpinator\Type\Container\Container::Boolean(),
                ];
            }

            public function getDirective(string $name) : \Graphpinator\Directive\Directive
            {
                return $this->getAllDirectives()[$name];
            }

            public function getAllDirectives() : array
            {
                return [
                    'skip' => \Graphpinator\Type\Container\Container::directiveSkip(),
                    'include' => \Graphpinator\Type\Container\Container::directiveInclude(),
                ];
            }
        };
    }

    public static function getQuery() : \Graphpinator\Type\Type
    {
        return new class extends \Graphpinator\Type\Type
        {
            protected const NAME = 'Query';

            protected function getFieldDefinition() : \Graphpinator\Field\ResolvableFieldSet
            {
                return new \Graphpinator\Field\ResolvableFieldSet([
                    new \Graphpinator\Field\ResolvableField('field0', TestSchema::getUnion(), function () {
                        return \Graphpinator\Resolver\FieldResult::fromRaw(TestSchema::getTypeAbc(), 1);
                    })
                ]);
            }
        };
    }

    public static function getTypeAbc() : \Graphpinator\Type\Type
    {
        return new class extends \Graphpinator\Type\Type
        {
            protected const NAME = 'Abc';
            protected const DESCRIPTION = 'Test Abc description';

            protected function validateNonNullValue($rawValue) : bool
            {
                return $rawValue === 1;
            }

            protected function getFieldDefinition() : \Graphpinator\Field\ResolvableFieldSet
            {
                return new \Graphpinator\Field\ResolvableFieldSet([
                    (new \Graphpinator\Field\ResolvableField('field1', TestSchema::getInterface(),
                        static function (int $parent, \Graphpinator\Normalizer\ArgumentValueSet $args) {
                            $object = new \stdClass();

                            if ($args['arg2']->getRawValue() === null) {
                                $object->name = 'Test ' . $args['arg1']->getRawValue();
                            } else {
                                $objectVal = $args['arg2']->getRawValue();
                                $str = '';

                                \array_walk_recursive($objectVal, function ($item, $key) use (&$str) {
                                    $str .= $key . ': ' . $item . '; ';
                                });

                                $object->name = 'Test input: ' . $str;
                            }

                            return \Graphpinator\Resolver\FieldResult::fromRaw(TestSchema::getTypeXyz(), $object);
                    },
                    new \Graphpinator\Argument\ArgumentSet([
                        new \Graphpinator\Argument\Argument('arg1', \Graphpinator\Type\Container\Container::Int(), 123),
                        new \Graphpinator\Argument\Argument('arg2', TestSchema::getInput()),
                    ])))->setDeprecated(true)->setDeprecationReason('Test deprecation reason')
                ]);
            }
        };
    }

    public static function getTypeXyz() : \Graphpinator\Type\Type
    {
        return new class extends \Graphpinator\Type\Type
        {
            protected const NAME = 'Xyz';
            protected const DESCRIPTION = null;

            public function __construct()
            {
                parent::__construct(new \Graphpinator\Utils\InterfaceSet([TestSchema::getInterface()]));
            }

            protected function getFieldDefinition() : \Graphpinator\Field\ResolvableFieldSet
            {
                return new \Graphpinator\Field\ResolvableFieldSet([
                    new \Graphpinator\Field\ResolvableField('name', \Graphpinator\Type\Container\Container::String()->notNull(), function (\stdClass $parent) {
                        return $parent->name;
                    })
                ]);
            }
        };
    }

    public static function getTypeZzz() : \Graphpinator\Type\Type
    {
        return new class extends \Graphpinator\Type\Type
        {
            protected const NAME = 'Zzz';
            protected const DESCRIPTION = null;

            protected function getFieldDefinition() : \Graphpinator\Field\ResolvableFieldSet
            {
                return new \Graphpinator\Field\ResolvableFieldSet([
                    new \Graphpinator\Field\ResolvableField('enumList', TestSchema::getEnum()->list(), function () {
                        return ['A', 'B'];
                    })
                ]);
            }
        };
    }

    public static function getInput() : \Graphpinator\Type\InputType
    {
        return new class extends \Graphpinator\Type\InputType
        {
            protected const NAME = 'TestInput';

            public function __construct()
            {
                parent::__construct(new \Graphpinator\Argument\ArgumentSet([
                    new \Graphpinator\Argument\Argument('name', \Graphpinator\Type\Container\Container::String()->notNull()),
                    new \Graphpinator\Argument\Argument('inner', TestSchema::getInnerInput()),
                    new \Graphpinator\Argument\Argument('innerList', TestSchema::getInnerInput()->notNullList()),
                    new \Graphpinator\Argument\Argument('innerNotNull', TestSchema::getInnerInput()->notNull()),
                ]));
            }
        };
    }

    public static function getInnerInput() : \Graphpinator\Type\InputType
    {
        return new class extends \Graphpinator\Type\InputType
        {
            protected const NAME = 'TestInnerInput';

            public function __construct()
            {
                parent::__construct(new \Graphpinator\Argument\ArgumentSet([
                    new \Graphpinator\Argument\Argument('name', \Graphpinator\Type\Container\Container::String()->notNull()),
                    new \Graphpinator\Argument\Argument('number', \Graphpinator\Type\Container\Container::Int()->notNullList()),
                    new \Graphpinator\Argument\Argument('bool', \Graphpinator\Type\Container\Container::Boolean()),
                ]));
            }
        };
    }

    public static function getInterface() : \Graphpinator\Type\InterfaceType
    {
        return new class extends \Graphpinator\Type\InterfaceType
        {
            protected const NAME = 'TestInterface';

            protected function getFieldDefinition(): \Graphpinator\Field\FieldSet
            {
                return new \Graphpinator\Field\FieldSet([
                    new \Graphpinator\Field\Field('name', \Graphpinator\Type\Container\Container::String()->notNull()),
                ]);
            }
        };
    }

    public static function getUnion() : \Graphpinator\Type\UnionType
    {
        return new class extends \Graphpinator\Type\UnionType
        {
            protected const NAME = 'TestUnion';

            public function __construct()
            {
                parent::__construct(new \Graphpinator\Utils\ConcreteSet([
                    TestSchema::getTypeAbc(),
                    TestSchema::getTypeXyz(),
                ]));
            }
        };
    }

    public static function getEnum() : \Graphpinator\Type\Scalar\EnumType
    {
        return new class extends \Graphpinator\Type\Scalar\EnumType
        {
            protected const NAME = 'TestEnum';

            public const A = 'a';
            public const B = 'b';
            public const C = 'c';
            public const D = 'd';

            public function __construct()
            {
                parent::__construct(self::fromConstants());
            }
        };
    }
}
