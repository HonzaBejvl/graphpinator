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

    public static function getFullSchema() : \Graphpinator\Type\Schema
    {
        return new \Graphpinator\Type\Schema(
            self::getTypeResolver(),
            self::getQuery(),
            self::getQuery(),
            self::getQuery(),
        );
    }

    public static function getTypeResolver() : \Graphpinator\Type\Container\Container
    {
        return new \Graphpinator\Type\Container\SimpleContainer([
            'Query' => self::getQuery(),
            'Abc' => self::getTypeAbc(),
            'Xyz' => self::getTypeXyz(),
            'Zzz' => self::getTypeZzz(),
            'TestInterface' => self::getInterface(),
            'TestUnion' => self::getUnion(),
            'TestInput' => self::getInput(),
            'TestInnerInput' => self::getInnerInput(),
            'ConstraintInput' => self::getConstraintInput(),
            'SimpleEnum' => self::getSimpleEnum(),
            'ArrayEnum' => self::getArrayEnum(),
            'DescriptionEnum' => self::getDescriptionEnum(),
            'TestScalar' => self::getTestScalar(),
        ], [
            'testDirective' => self::getTestDirective(),
            'invalidDirective' => self::getInvalidDirective(),
        ]);
    }

    public static function getQuery() : \Graphpinator\Type\Type
    {
        return new class extends \Graphpinator\Type\Type
        {
            protected const NAME = 'Query';

            protected function getFieldDefinition() : \Graphpinator\Field\ResolvableFieldSet
            {
                return new \Graphpinator\Field\ResolvableFieldSet([
                    new \Graphpinator\Field\ResolvableField(
                        'fieldValid',
                        TestSchema::getUnion(),
                        static function () {
                            return \Graphpinator\Resolver\FieldResult::fromRaw(TestSchema::getTypeAbc(), 1);
                        },
                    ),
                    new \Graphpinator\Field\ResolvableField(
                        'fieldConstraint',
                        \Graphpinator\Type\Container\Container::Int(),
                        static function ($parent, \stdClass $arg) : int {
                            return 1;
                        },
                        new \Graphpinator\Argument\ArgumentSet([
                            new \Graphpinator\Argument\Argument(
                                'arg',
                                TestSchema::getConstraintInput(),
                            )
                        ]),
                    ),
                    new \Graphpinator\Field\ResolvableField(
                        'fieldInvalidType',
                        TestSchema::getUnion(),
                        static function () {
                            return \Graphpinator\Resolver\FieldResult::fromRaw(\Graphpinator\Type\Container\Container::Int(), 1);
                        },
                    ),
                    new \Graphpinator\Field\ResolvableField(
                        'fieldInvalidReturn',
                        TestSchema::getUnion(),
                        static function () {
                            return 1;
                        },
                    ),
                    new \Graphpinator\Field\ResolvableField('fieldThrow',
                        TestSchema::getUnion(),
                        static function () : void {
                            throw new \Exception('Random exception');
                        },
                    ),
                ]);
            }

            protected function validateNonNullValue($rawValue) : bool
            {
                return true;
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
                    (new \Graphpinator\Field\ResolvableField(
                        'field1',
                        TestSchema::getInterface(),
                        static function (int $parent, ?int $arg1, ?\stdClass $arg2) : \Graphpinator\Resolver\FieldResult {
                            $object = new \stdClass();

                            if ($arg2 === null) {
                                $object->name = 'Test ' . $arg1;
                            } else {
                                $concat = static function (\stdClass $objectVal) use (&$concat) : string {
                                    $str = '';

                                    foreach ($objectVal as $key => $item) {
                                        if ($item instanceof \stdClass) {
                                            $print = '{' . $concat($item) . '}';
                                        } elseif (\is_array($item)) {
                                            $print = '[]';
                                        } elseif (\is_scalar($item)) {
                                            $print = $item;
                                        } elseif ($item === null) {
                                            $print = 'null';
                                        }

                                        $str .= $key . ': ' . $print . '; ';
                                    }

                                    return $str;
                                };

                                $object->name = $concat($arg2);
                            }

                            return \Graphpinator\Resolver\FieldResult::fromRaw(TestSchema::getTypeXyz(), $object);
                        },
                        new \Graphpinator\Argument\ArgumentSet([
                            new \Graphpinator\Argument\Argument('arg1', \Graphpinator\Type\Container\Container::Int(), 123),
                            new \Graphpinator\Argument\Argument('arg2', TestSchema::getInput()),
                        ]),
                    ))->setDeprecated(true),
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
                    new \Graphpinator\Field\ResolvableField(
                        'name',
                        \Graphpinator\Type\Container\Container::String()->notNull(),
                        static function (\stdClass $parent) {
                            return $parent->name;
                        },
                    ),
                ]);
            }

            protected function validateNonNullValue($rawValue) : bool
            {
                return true;
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
                    new \Graphpinator\Field\ResolvableField('enumList', TestSchema::getSimpleEnum()->list(), static function () {
                        return ['A', 'B'];
                    }),
                ]);
            }

            protected function validateNonNullValue($rawValue) : bool
            {
                return true;
            }
        };
    }

    public static function getInput() : \Graphpinator\Type\InputType
    {
        return new class extends \Graphpinator\Type\InputType
        {
            protected const NAME = 'TestInput';

            protected function getFieldDefinition() : \Graphpinator\Argument\ArgumentSet
            {
                return new \Graphpinator\Argument\ArgumentSet([
                    new \Graphpinator\Argument\Argument(
                        'name',
                        \Graphpinator\Type\Container\Container::String()->notNull(),
                    ),
                    new \Graphpinator\Argument\Argument(
                        'inner',
                        TestSchema::getInnerInput(),
                    ),
                    new \Graphpinator\Argument\Argument(
                        'innerList',
                        TestSchema::getInnerInput()->notNullList(),
                    ),
                    new \Graphpinator\Argument\Argument(
                        'innerNotNull',
                        TestSchema::getInnerInput()->notNull(),
                    ),
                ]);
            }
        };
    }

    public static function getInnerInput() : \Graphpinator\Type\InputType
    {
        return new class extends \Graphpinator\Type\InputType
        {
            protected const NAME = 'TestInnerInput';

            protected function getFieldDefinition() : \Graphpinator\Argument\ArgumentSet
            {
                return new \Graphpinator\Argument\ArgumentSet([
                    new \Graphpinator\Argument\Argument(
                        'name',
                        \Graphpinator\Type\Container\Container::String()->notNull(),
                    ),
                    new \Graphpinator\Argument\Argument(
                        'number',
                        \Graphpinator\Type\Container\Container::Int()->notNullList(),
                    ),
                    new \Graphpinator\Argument\Argument(
                        'bool',
                        \Graphpinator\Type\Container\Container::Boolean(),
                    ),
                ]);
            }
        };
    }

    public static function getConstraintInput() : \Graphpinator\Type\InputType
    {
        return new class extends \Graphpinator\Type\InputType
        {
            protected const NAME = 'ConstraintInput';

            protected function getFieldDefinition(): \Graphpinator\Argument\ArgumentSet
            {
                return new \Graphpinator\Argument\ArgumentSet([
                    (new \Graphpinator\Argument\Argument(
                        'intMinArg',
                        \Graphpinator\Type\Container\Container::Int(),
                    ))->setConstraint(new \Graphpinator\Argument\Constraint\IntConstraint(-20)),
                    (new \Graphpinator\Argument\Argument(
                        'intMaxArg',
                        \Graphpinator\Type\Container\Container::Int(),
                    ))->setConstraint(new \Graphpinator\Argument\Constraint\IntConstraint(null, 20)),
                    (new \Graphpinator\Argument\Argument(
                        'intOneOfArg',
                        \Graphpinator\Type\Container\Container::Int(),
                    ))->setConstraint(new \Graphpinator\Argument\Constraint\IntConstraint(null, null, [1, 2, 3])),
                    (new \Graphpinator\Argument\Argument(
                        'floatMinArg',
                        \Graphpinator\Type\Container\Container::Float(),
                    ))->setConstraint(new \Graphpinator\Argument\Constraint\FloatConstraint(4.01)),
                    (new \Graphpinator\Argument\Argument(
                        'floatMaxArg',
                        \Graphpinator\Type\Container\Container::Float(),
                    ))->setConstraint(new \Graphpinator\Argument\Constraint\FloatConstraint(null, 20.101)),
                    (new \Graphpinator\Argument\Argument(
                        'floatOneOfArg',
                        \Graphpinator\Type\Container\Container::Float(),
                    ))->setConstraint(new \Graphpinator\Argument\Constraint\FloatConstraint(null, null, [1.01, 2.02, 3.0])),
                    (new \Graphpinator\Argument\Argument(
                        'stringMinArg',
                        \Graphpinator\Type\Container\Container::String(),
                    ))->setConstraint(new \Graphpinator\Argument\Constraint\StringConstraint(4)),
                    (new \Graphpinator\Argument\Argument(
                        'stringMaxArg',
                        \Graphpinator\Type\Container\Container::String(),
                    ))->setConstraint(new \Graphpinator\Argument\Constraint\StringConstraint(null, 10)),
                    (new \Graphpinator\Argument\Argument(
                        'stringRegexArg',
                        \Graphpinator\Type\Container\Container::String(),
                    ))->setConstraint(new \Graphpinator\Argument\Constraint\StringConstraint(null, null, '/^(abc)|(foo)$/')),
                    (new \Graphpinator\Argument\Argument(
                        'stringOneOfArg',
                        \Graphpinator\Type\Container\Container::String(),
                    ))->setConstraint(new \Graphpinator\Argument\Constraint\StringConstraint(null, null, null, ['abc', 'foo'])),
                    (new \Graphpinator\Argument\Argument(
                        'stringOneOfEmptyArg',
                        \Graphpinator\Type\Container\Container::String(),
                    ))->setConstraint(new \Graphpinator\Argument\Constraint\StringConstraint(null, null, null, [])),
                    (new \Graphpinator\Argument\Argument(
                        'listMinArg',
                        \Graphpinator\Type\Container\Container::Int()->list(),
                    ))->setConstraint(new \Graphpinator\Argument\Constraint\ListConstraint(1)),
                    (new \Graphpinator\Argument\Argument(
                        'listMaxArg',
                        \Graphpinator\Type\Container\Container::Int()->list(),
                    ))->setConstraint(new \Graphpinator\Argument\Constraint\ListConstraint(null, 3)),
                    (new \Graphpinator\Argument\Argument(
                        'listUniqueArg',
                        \Graphpinator\Type\Container\Container::Int()->list(),
                    ))->setConstraint(new \Graphpinator\Argument\Constraint\ListConstraint(null, null, true)),
                    (new \Graphpinator\Argument\Argument(
                        'listInnerListArg',
                        \Graphpinator\Type\Container\Container::Int()->list()->list(),
                    ))->setConstraint(new \Graphpinator\Argument\Constraint\ListConstraint(null, null, false, (object) [
                        'minItems' => 1,
                        'maxItems' => 3,
                    ])),
                ]);
            }
        };
    }

    public static function getInterface() : \Graphpinator\Type\InterfaceType
    {
        return new class extends \Graphpinator\Type\InterfaceType
        {
            protected const NAME = 'TestInterface';
            protected const DESCRIPTION = 'TestInterface Description';

            protected function getFieldDefinition() : \Graphpinator\Field\FieldSet
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

    public static function getSimpleEnum() : \Graphpinator\Type\EnumType
    {
        return new class extends \Graphpinator\Type\EnumType
        {
            public const A = 'A';
            public const B = 'B';
            public const C = 'C';
            public const D = 'D';

            protected const NAME = 'SimpleEnum';

            public function __construct()
            {
                parent::__construct(self::fromConstants());
            }
        };
    }

    public static function getArrayEnum() : \Graphpinator\Type\EnumType
    {
        return new class extends \Graphpinator\Type\EnumType
        {
            public const A = ['A', 'First description'];
            public const B = ['B', 'Second description'];
            public const C = ['C', 'Third description'];

            protected const NAME = 'ArrayEnum';

            public function __construct()
            {
                parent::__construct(self::fromConstants());
            }
        };
    }

    public static function getDescriptionEnum() : \Graphpinator\Type\EnumType
    {
        return new class extends \Graphpinator\Type\EnumType
        {
            protected const NAME = 'DescriptionEnum';

            public function __construct()
            {
                parent::__construct(new \Graphpinator\Type\Enum\EnumItemSet([
                    new \Graphpinator\Type\Enum\EnumItem('A', 'single line description'),
                    (new \Graphpinator\Type\Enum\EnumItem('B'))
                        ->setDeprecated(true),
                    new \Graphpinator\Type\Enum\EnumItem('C', 'multi line' . \PHP_EOL . 'description'),
                    (new \Graphpinator\Type\Enum\EnumItem('D', 'single line description'))
                        ->setDeprecated(true)
                        ->setDeprecationReason('reason'),
                ]));
            }
        };
    }

    public static function getTestScalar() : \Graphpinator\Type\Scalar\ScalarType
    {
        return new class extends \Graphpinator\Type\Scalar\ScalarType
        {
            protected const NAME = 'TestScalar';

            protected function validateNonNullValue($rawValue) : bool
            {
                return true;
            }
        };
    }

    public static function getTestDirective() : \Graphpinator\Directive\Directive
    {
        return new class extends \Graphpinator\Directive\Directive
        {
            protected const NAME = 'testDirective';
            public static $count = 0;

            public function __construct()
            {
                parent::__construct(
                    new \Graphpinator\Argument\ArgumentSet([]),
                    static function() {
                        ++self::$count;

                        return \Graphpinator\Directive\DirectiveResult::NONE;
                    },
                    [\Graphpinator\Directive\DirectiveLocation::FIELD],
                    true,
                );
            }
        };
    }

    public static function getInvalidDirective() : \Graphpinator\Directive\Directive
    {
        return new class extends \Graphpinator\Directive\Directive
        {
            protected const NAME = 'invalidDirective';

            public function __construct()
            {
                parent::__construct(
                    new \Graphpinator\Argument\ArgumentSet([]),
                    static function() {
                        return 'blahblah';
                    },
                    [\Graphpinator\Directive\DirectiveLocation::FIELD],
                    true,
                );
            }
        };
    }
}
