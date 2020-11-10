<?php

declare(strict_types = 1);

namespace Graphpinator\Normalizer;

/**
 * @method \Graphpinator\Normalizer\Field current() : object
 * @method \Graphpinator\Normalizer\Field offsetGet($offset) : object
 */
final class FieldSet extends \Infinityloop\Utils\ObjectSet
{
    protected const INNER_CLASS = Field::class;

    protected array $fieldsForName = [];

    public function applyVariables(\Graphpinator\Resolver\VariableValueSet $variables) : self
    {
        $fields = [];

        foreach ($this as $field) {
            $fields[] = $field->applyVariables($variables);
        }

        return new self($fields);
    }

    public function mergeFieldSet(
        \Graphpinator\Type\Contract\NamedDefinition $parentType,
        \Graphpinator\Normalizer\FieldSet $fieldSet
    ) : void
    {
        foreach ($fieldSet as $field) {
            if (!\array_key_exists($field->getAlias(), $this->fieldsForName)) {
                $this->offsetSet(null, $field);

                continue;
            }

            $this->mergeConflictingField($parentType, $field);
        }
    }

    public function offsetSet($offset, $object) : void
    {
        \assert($object instanceof Field);

        if (!\array_key_exists($object->getAlias(), $this->fieldsForName)) {
            $this->fieldsForName[$object->getAlias()] = [];
        }

        $this->fieldsForName[$object->getAlias()][] = $object;

        parent::offsetSet($offset, $object);
    }

    private function mergeConflictingField(
        \Graphpinator\Type\Contract\NamedDefinition $parentType,
        \Graphpinator\Normalizer\Field $field
    ) : void
    {
        $fieldArguments = $field->getArguments();
        $fieldParentType = $field->getTypeCondition()
            ?? $parentType;
        $fieldReturnType = $fieldParentType->getField($field->getName())->getType();

        foreach ($this->fieldsForName[$field->getAlias()] as $conflict) {
            \assert($conflict instanceof Field);

            $conflictArguments = $conflict->getArguments();
            $conflictParentType = $conflict->getTypeCondition()
                ?? $parentType;
            $conflictReturnType = $conflictParentType->getField($conflict->getName())->getType();

            if (!$fieldReturnType->isInstanceOf($conflictReturnType) ||
                !$conflictReturnType->isInstanceOf($fieldReturnType)) {
                throw new \Graphpinator\Exception\Normalizer\ConflictingFieldType();
            }

            if ($conflictParentType->isInstanceOf($fieldParentType) &&
                $fieldParentType->isInstanceOf($conflictParentType)) {
                if ($field->getName() !== $conflict->getName()) {
                    throw new \Graphpinator\Exception\Normalizer\ConflictingFieldAlias();
                }

                if ($fieldArguments->count() !== $conflictArguments->count()) {
                    throw new \Graphpinator\Exception\Normalizer\ConflictingFieldArguments();
                }

                foreach ($conflictArguments as $lhs) {
                    if ($fieldArguments->offsetExists($lhs->getName()) &&
                        $lhs->getValue()->isSame($fieldArguments[$lhs->getName()]->getValue())) {
                        continue;
                    }

                    throw new \Graphpinator\Exception\Normalizer\ConflictingFieldArguments();
                }

                if ($conflict->getFields() instanceof self) {
                    $conflict->getFields()->mergeFieldSet($conflictReturnType, $field->getFields());
                }

                return;
            }
        }

        $this->offsetSet(null, $field);
    }
}
