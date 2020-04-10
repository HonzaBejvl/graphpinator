<?php

declare(strict_types = 1);

namespace Graphpinator\Type;

abstract class Type extends \Graphpinator\Type\Contract\ConcreteDefinition implements
    \Graphpinator\Type\Contract\Resolvable,
    \Graphpinator\Type\Contract\InterfaceImplementor
{
    use \Graphpinator\Type\Contract\TResolvable;
    use \Graphpinator\Type\Contract\TInterfaceImplementor;

    protected ?\Graphpinator\Field\ResolvableFieldSet $metaFields = null;

    public function __construct(?\Graphpinator\Utils\InterfaceSet $implements = null)
    {
        $this->implements = $implements ?? new \Graphpinator\Utils\InterfaceSet([]);
    }

    public function createValue($rawValue) : \Graphpinator\Value\ValidatedValue
    {
        return \Graphpinator\Value\TypeValue::create($rawValue, $this);
    }

    public function isInstanceOf(\Graphpinator\Type\Contract\Definition $type) : bool
    {
        if ($type instanceof \Graphpinator\Type\Contract\AbstractDefinition) {
            return $type->isImplementedBy($this);
        }

        return parent::isInstanceOf($type);
    }

    public function resolveFields(?\Graphpinator\Normalizer\FieldSet $requestedFields, \Graphpinator\Resolver\FieldResult $parentResult) : array
    {
        if ($requestedFields === null) {
            throw new \Exception('Composite type without fields specified.');
        }

        $resolved = [];

        foreach ($requestedFields as $request) {
            if (!$request->typeMatches($parentResult->getType())) {
                continue;
            }

            $field = $this->getMetaFields()[$request->getName()] ?? $this->getFields()[$request->getName()];
            $arguments = new \Graphpinator\Normalizer\ArgumentValueSet($request->getArguments(), $field->getArguments());
            $innerResult = $field->resolve($parentResult, $arguments);

            $resolved[$request->getAlias()] = $innerResult->getResult() instanceof \Graphpinator\Value\NullValue
                ? $innerResult->getResult()
                : $innerResult->getType()->resolveFields($request->getFields(), $innerResult);
        }

        return $resolved;
    }

    public function getTypeKind() : string
    {
        return \Graphpinator\Type\Introspection\TypeKind::OBJECT;
    }

    public function getMetaFields() : \Graphpinator\Field\ResolvableFieldSet
    {
        if (!$this->metaFields instanceof \Graphpinator\Field\ResolvableFieldSet) {
            $this->metaFields = $this->getMetaFieldDefinition();
        }

        return $this->metaFields;
    }

    public function addMetaField(\Graphpinator\Field\ResolvableField $field) : void
    {
        $this->getMetaFields()->offsetSet($field->getName(), $field);
    }

    abstract protected function getFieldDefinition() : \Graphpinator\Field\ResolvableFieldSet;

    private function getMetaFieldDefinition() : \Graphpinator\Field\ResolvableFieldSet
    {
        return new \Graphpinator\Field\ResolvableFieldSet([
            new \Graphpinator\Field\ResolvableField(
                '__typename',
                \Graphpinator\Type\Container\Container::String()->notNull(),
                function() { return $this->getName(); },
            ),
        ]);
    }
}
