<?php

declare(strict_types = 1);

namespace Graphpinator\Tests\Spec;

final class SimpleTest extends \PHPUnit\Framework\TestCase
{
    public function simpleDataProvider() : array
    {
        return [
            [
                \Graphpinator\Json::fromObject((object) [
                    'query' => 'query queryName { fieldUnion { field1 { name } } }',
                ]),
                \Graphpinator\Json::fromObject((object) ['data' => ['fieldUnion' => ['field1' => ['name' => 'Test 123']]]]),
            ],
            [
                \Graphpinator\Json::fromObject((object) [
                    'query' => 'query queryName { aliasName: fieldUnion { field1 { name } } }',
                ]),
                \Graphpinator\Json::fromObject((object) ['data' => ['aliasName' => ['field1' => ['name' => 'Test 123']]]]),
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
        $result = $graphpinator->run(new \Graphpinator\Request\JsonRequestFactory($request));

        self::assertSame($expected->toString(), $result->toString());
    }

    /**
     * @dataProvider simpleDataProvider
     * @param \Graphpinator\Json $request
     * @param \Graphpinator\Json $expected
     */
    public function testComponents(\Graphpinator\Json $request, \Graphpinator\Json $expected) : void
    {
        $source = new \Graphpinator\Source\StringSource($request['query']);
        $parser = new \Graphpinator\Parser\Parser($source);

        $operationName = $request['operationName']
            ?? null;
        $variables = $request['variables']
            ?? new \stdClass();

        $result = $parser->parse()->normalize(TestSchema::getSchema())->createRequest($operationName, $variables)->execute();

        self::assertSame($expected->toString(), $result->toString());
    }

    /**
     * @dataProvider simpleDataProvider
     * @param \Graphpinator\Json $request
     * @param \Graphpinator\Json $expected
     */
    public function testHttpJsonBody(\Graphpinator\Json $request, \Graphpinator\Json $expected) : void
    {
        $stream = $this->createStub(\Psr\Http\Message\StreamInterface::class);
        $stream->method('getContents')->willReturn($request->toString());
        $httpRequest = $this->createStub(\Psr\Http\Message\ServerRequestInterface::class);
        $httpRequest->method('getHeader')->willReturn(['application/json']);
        $httpRequest->method('getBody')->willReturn($stream);
        $httpRequest->method('getMethod')->willReturn('GET');

        $graphpinator = new \Graphpinator\Graphpinator(TestSchema::getSchema());
        $result = $graphpinator->run(new \Graphpinator\Request\PsrRequestFactory($httpRequest));

        self::assertSame($expected->toString(), $result->toString());
    }

    /**
     * @dataProvider simpleDataProvider
     * @param \Graphpinator\Json $request
     * @param \Graphpinator\Json $expected
     */
    public function testHttpJsonBodyPost(\Graphpinator\Json $request, \Graphpinator\Json $expected) : void
    {
        $stream = $this->createStub(\Psr\Http\Message\StreamInterface::class);
        $stream->method('getContents')->willReturn($request->toString());
        $httpRequest = $this->createStub(\Psr\Http\Message\ServerRequestInterface::class);
        $httpRequest->method('getHeader')->willReturn(['application/json']);
        $httpRequest->method('getBody')->willReturn($stream);
        $httpRequest->method('getMethod')->willReturn('POST');

        $graphpinator = new \Graphpinator\Graphpinator(TestSchema::getSchema());
        $result = $graphpinator->run(new \Graphpinator\Request\PsrRequestFactory($httpRequest));

        self::assertSame($expected->toString(), $result->toString());
    }

    /**
     * @dataProvider simpleDataProvider
     * @param \Graphpinator\Json $request
     * @param \Graphpinator\Json $expected
     */
    public function testHttpGraphQlBody(\Graphpinator\Json $request, \Graphpinator\Json $expected) : void
    {
        $stream = $this->createStub(\Psr\Http\Message\StreamInterface::class);
        $stream->method('getContents')->willReturn($request['query']);
        $httpRequest = $this->createStub(\Psr\Http\Message\ServerRequestInterface::class);
        $httpRequest->method('getHeader')->willReturn(['application/graphql']);
        $httpRequest->method('getBody')->willReturn($stream);
        $httpRequest->method('getMethod')->willReturn('GET');

        $graphpinator = new \Graphpinator\Graphpinator(TestSchema::getSchema());
        $result = $graphpinator->run(new \Graphpinator\Request\PsrRequestFactory($httpRequest));

        self::assertSame($expected->toString(), $result->toString());
    }

    /**
     * @dataProvider simpleDataProvider
     * @param \Graphpinator\Json $request
     * @param \Graphpinator\Json $expected
     */
    public function testHttpParamsBody(\Graphpinator\Json $request, \Graphpinator\Json $expected) : void
    {
        $params = (array) $request->toObject();

        if (\array_key_exists('variables', $params)) {
            $params['variables'] = \Graphpinator\Json::fromObject($params['variables'])->toString();
        }

        $httpRequest = $this->createStub(\Psr\Http\Message\ServerRequestInterface::class);
        $httpRequest->method('getHeader')->willReturn([]);
        $httpRequest->method('getQueryParams')->willReturn((array) $request->toObject());
        $httpRequest->method('getMethod')->willReturn('GET');

        $graphpinator = new \Graphpinator\Graphpinator(TestSchema::getSchema());
        $result = $graphpinator->run(new \Graphpinator\Request\PsrRequestFactory($httpRequest));

        self::assertSame($expected->toString(), $result->toString());
    }

    public function invalidDataProvider() : array
    {
        return [
            [
                \Graphpinator\Json::fromObject((object) [
                    'query' => 'query queryName { fieldUnion { field1 } }',
                ]),
                \Graphpinator\Exception\Resolver\SelectionOnComposite::class,
            ],
            [
                \Graphpinator\Json::fromObject((object) [
                    'query' => 'query queryName { fieldUnion { field1 { nonExisting } } }',
                ]),
                //phpcs:ignore SlevomatCodingStandard.Exceptions.ReferenceThrowableOnly.ReferencedGeneralException
                \Exception::class,
            ],
            [
                \Graphpinator\Json::fromObject((object) [
                    'query' => 'query queryName { fieldUnion { field1 { name { nonExisting } } } }',
                ]),
                //phpcs:ignore SlevomatCodingStandard.Exceptions.ReferenceThrowableOnly.ReferencedGeneralException
                \Exception::class,
            ],
            [
                \Graphpinator\Json::fromObject((object) [
                    'query' => 'query queryName { fieldInvalidType { } }',
                ]),
                \Graphpinator\Exception\Resolver\FieldResultTypeMismatch::class,
            ],
            [
                \Graphpinator\Json::fromObject((object) []),
                \Graphpinator\Exception\Request\QueryMissing::class,
            ],
            [
                \Graphpinator\Json::fromObject((object) [
                    'query' => 123,
                ]),
                \Graphpinator\Exception\Request\QueryNotString::class,
            ],
            [
                \Graphpinator\Json::fromObject((object) [
                    'query' => '',
                    'variables' => 'abc',
                ]),
                \Graphpinator\Exception\Request\VariablesNotObject::class,
            ],
            [
                \Graphpinator\Json::fromObject((object) [
                    'query' => '',
                    'operationName' => 123,
                ]),
                \Graphpinator\Exception\Request\OperationNameNotString::class,
            ],
            [
                \Graphpinator\Json::fromObject((object) [
                    'query' => '',
                    'operationName' => '',
                    'randomKey' => 'randomVal',
                ]),
                \Graphpinator\Exception\Request\UnknownKey::class,
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

        $graphpinator = new \Graphpinator\Graphpinator(TestSchema::getSchema());
        $graphpinator->run(new \Graphpinator\Request\JsonRequestFactory($request));
    }
}
