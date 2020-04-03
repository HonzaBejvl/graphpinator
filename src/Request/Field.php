<?php

declare(strict_types = 1);

namespace Graphpinator\Request;

final class Field
{
    use \Nette\SmartObject;

    private string $name;
    private string $alias;
    private ?FieldSet $children;
    private \Graphpinator\Value\ArgumentValueSet $arguments;
    private ?\Graphpinator\Type\Contract\NamedDefinition $conditionType;

    public function __construct(
        string $name,
        ?string $alias,
        ?FieldSet $children,
        ?\Graphpinator\Value\ArgumentValueSet $arguments,
        ?\Graphpinator\Type\Contract\NamedDefinition $conditionType
    ) {
        $this->name = $name;
        $this->alias = $alias ?? $name;
        $this->children = $children;
        $this->arguments = $arguments;
        $this->conditionType = $conditionType;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getAlias() : string
    {
        return $this->alias;
    }

    public function getChildren() : ?FieldSet
    {
        return $this->children;
    }

    public function getConditionType() : ?\Graphpinator\Type\Contract\NamedDefinition
    {
        return $this->conditionType;
    }

    public function getArguments() : \Graphpinator\Value\ArgumentValueSet
    {
        return $this->arguments;
    }
}
