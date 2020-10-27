<?php

declare(strict_types = 1);

namespace Graphpinator\Tests\Spec;

final class DirectiveTest extends \PHPUnit\Framework\TestCase
{
    public function simpleDataProvider() : array
    {
        return [
            [
                \Graphpinator\Json::fromObject((object) [
                    'query' => 'query queryName { fieldUnion { fieldXyz @skip(if: true) { name } } }',
                ]),
                \Graphpinator\Json::fromObject((object) ['data' => ['fieldUnion' => new \stdClass()]]),
            ],
            [
                \Graphpinator\Json::fromObject((object) [
                    'query' => 'query queryName { fieldUnion { fieldXyz @skip(if: false) { name } } }',
                ]),
                \Graphpinator\Json::fromObject((object) ['data' => ['fieldUnion' => ['fieldXyz' => ['name' => 'Test 123']]]]),
            ],
            [
                \Graphpinator\Json::fromObject((object) [
                    'query' => 'query queryName { fieldUnion { fieldXyz @include(if: true) { name } } }',
                ]),
                \Graphpinator\Json::fromObject((object) ['data' => ['fieldUnion' => ['fieldXyz' => ['name' => 'Test 123']]]]),
            ],
            [
                \Graphpinator\Json::fromObject((object) [
                    'query' => 'query queryName { fieldUnion { fieldXyz @include(if: false) { name } } }',
                ]),
                \Graphpinator\Json::fromObject((object) ['data' => ['fieldUnion' => new \stdClass()]]),
            ],
            [
                \Graphpinator\Json::fromObject((object) [
                    'query' => 'query queryName { fieldUnion { fieldXyz @include(if: false) @skip(if: false) { name } } }',
                ]),
                \Graphpinator\Json::fromObject((object) ['data' => ['fieldUnion' => new \stdClass()]]),
            ],
            [
                \Graphpinator\Json::fromObject((object) [
                    'query' => 'query queryName { fieldUnion { fieldXyz @include(if: true) @skip(if: true) { name } } }',
                ]),
                \Graphpinator\Json::fromObject((object) ['data' => ['fieldUnion' => new \stdClass()]]),
            ],
            [
                \Graphpinator\Json::fromObject((object) [
                    'query' => 'query queryName { fieldUnion { fieldXyz @include(if: false) @skip(if: true) { name } } }',
                ]),
                \Graphpinator\Json::fromObject((object) ['data' => ['fieldUnion' => new \stdClass()]]),
            ],
            [
                \Graphpinator\Json::fromObject((object) [
                    'query' => 'query queryName { fieldUnion { fieldXyz @include(if: true) @skip(if: false) { name } } }',
                ]),
                \Graphpinator\Json::fromObject((object) ['data' => ['fieldUnion' => ['fieldXyz' => ['name' => 'Test 123']]]]),
            ],
            [
                \Graphpinator\Json::fromObject((object) [
                    'query' => 'query queryName { fieldUnion { ... @include(if: true) { fieldXyz { name } } } }',
                ]),
                \Graphpinator\Json::fromObject((object) ['data' => ['fieldUnion' => ['fieldXyz' => ['name' => 'Test 123']]]]),
            ],
            [
                \Graphpinator\Json::fromObject((object) [
                    'query' => 'query queryName { fieldUnion { ... @include(if: false) { fieldXyz { name } } } }',
                ]),
                \Graphpinator\Json::fromObject((object) ['data' => ['fieldUnion' => new \stdClass()]]),
            ],
            [
                \Graphpinator\Json::fromObject((object) [
                    'query' => 'query queryName { 
                        fieldUnion { ... namedFragment @include(if: true) } } fragment namedFragment on Abc { fieldXyz { name } 
                    }',
                ]),
                \Graphpinator\Json::fromObject((object) ['data' => ['fieldUnion' => ['fieldXyz' => ['name' => 'Test 123']]]]),
            ],
            [
                \Graphpinator\Json::fromObject((object) [
                    'query' => 'query queryName { 
                        fieldUnion { ... namedFragment @include(if: false) } } fragment namedFragment on Abc { fieldXyz { name } 
                    }',
                ]),
                \Graphpinator\Json::fromObject((object) ['data' => ['fieldUnion' => new \stdClass()]]),
            ],
            [
                \Graphpinator\Json::fromObject((object) [
                    'query' => 'query queryName { fieldUnion { fieldXyz @testDirective() { name } } }',
                ]),
                \Graphpinator\Json::fromObject((object) ['data' => ['fieldUnion' => ['fieldXyz' => ['name' => 'Test 123']]]]),
            ],
        ];
    }

    /**
     * @dataProvider simpleDataProvider
     * @param \Graphpinator\Json $request
     * @param \Graphpinator\Json $expected
     */
    public function testSimple(\Graphpinator\Json $request, \Graphpinator\Json $expected) : void
    {
        $graphpinator = new \Graphpinator\Graphpinator(TestSchema::getSchema());
        $result = $graphpinator->run(\Graphpinator\Request::fromJson($request));

        self::assertSame($expected->toString(), $result->toString());
        self::assertNull($result->getErrors());
    }

    public function testRepeatable() : void
    {
        $graphpinator = new \Graphpinator\Graphpinator(TestSchema::getSchema());
        TestSchema::getSchema()->getContainer()->getDirective('testDirective')::$count = 0;

        self::assertSame(
            \Graphpinator\Json::fromObject((object) ['data' => ['fieldUnion' => ['fieldXyz' => ['name' => 'Test 123']]]])->toString(),
            $graphpinator->run(
                \Graphpinator\Request::fromJson(
                    \Graphpinator\Json::fromObject((object) [
                        'query' => 'query queryName { fieldUnion { fieldXyz @testDirective @testDirective @testDirective { name } } }',
                    ]),
                ),
            )->toString(),
        );
        self::assertSame(3, TestSchema::getSchema()->getContainer()->getDirective('testDirective')::$count);
    }

    public function invalidDataProvider() : array
    {
        return [
            [
                \Graphpinator\Json::fromObject((object) [
                    'query' => 'query queryName { fieldUnion { fieldXyz @skip(if: false) @skip(if: false) { name } } }',
                ]),
                \Graphpinator\Exception\Normalizer\DuplicatedDirective::class,
            ],
            [
                \Graphpinator\Json::fromObject((object) [
                    'query' => 'query queryName { fieldUnion { fieldXyz @include(if: false) @include(if: false) { name } } }',
                ]),
                \Graphpinator\Exception\Normalizer\DuplicatedDirective::class,
            ],
            [
                \Graphpinator\Json::fromObject((object) [
                    'query' => 'query queryName { fieldUnion { fieldXyz @include(if: false) @testDirective @include(if: false) { name } } }',
                ]),
                \Graphpinator\Exception\Normalizer\DuplicatedDirective::class,
            ],
            [
                \Graphpinator\Json::fromObject((object) [
                    'query' => 'query queryName { fieldUnion { ... on Abc @testDirective { fieldXyz { name } } } }',
                ]),
                \Graphpinator\Exception\Normalizer\MisplacedDirective::class,
            ],
            [
                \Graphpinator\Json::fromObject((object) [
                    'query' => 'query queryName { fieldUnion @invalidDirective() { fieldXyz { name } } }',
                ]),
                \Graphpinator\Exception\Resolver\InvalidDirectiveResult::class,
            ],
            [
                \Graphpinator\Json::fromObject((object) [
                    'query' => 'query queryName { fieldUnion { fieldXyz @testDirective(if: true) { name } } }',
                ]),
                \Graphpinator\Exception\Resolver\UnknownArgument::class,
            ],
        ];
    }

    /**
     * @dataProvider invalidDataProvider
     * @param \Graphpinator\Json $request
     * @param string $exception
     */
    public function testInvalid(\Graphpinator\Json $request, string $exception) : void
    {
        $this->expectException($exception);
        $this->expectExceptionMessage(\constant($exception . '::MESSAGE'));

        $graphpinator = new \Graphpinator\Graphpinator(TestSchema::getSchema());
        $graphpinator->run(\Graphpinator\Request::fromJson($request));
    }
}
