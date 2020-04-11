<?php

declare(strict_types = 1);

namespace Graphpinator\Type;

final class Schema
{
    use \Nette\SmartObject;
    use \Graphpinator\Utils\TOptionalDescription;

    private \Graphpinator\Type\Container\Container $typeContainer;
    private \Graphpinator\Type\Type $query;
    private ?\Graphpinator\Type\Type $mutation;
    private ?\Graphpinator\Type\Type $subscription;

    public function __construct(
        \Graphpinator\Type\Container\Container $typeContainer,
        \Graphpinator\Type\Type $query,
        ?\Graphpinator\Type\Type $mutation = null,
        ?\Graphpinator\Type\Type $subscription = null
    )
    {
        $this->typeContainer = $typeContainer;
        $this->query = $query;
        $this->mutation = $mutation;
        $this->subscription = $subscription;

        $this->query->addMetaField(new \Graphpinator\Field\ResolvableField(
            '__schema',
            \Graphpinator\Type\Container\Container::introspectionSchema(),
            function() : self { return $this; },
        ));
        $this->query->addMetaField(new \Graphpinator\Field\ResolvableField(
            '__type',
            \Graphpinator\Type\Container\Container::introspectionType(),
            function($parent, \Graphpinator\Normalizer\ArgumentValueSet $args) : \Graphpinator\Type\Contract\Definition {
                return $this->typeContainer->getType($args['name']->getRawValue());
            },
            new \Graphpinator\Argument\ArgumentSet([
                new \Graphpinator\Argument\Argument('name', \Graphpinator\Type\Container\Container::String()->notNull()),
            ]),
        ));
    }

    public function getTypeContainer() : \Graphpinator\Type\Container\Container
    {
        return $this->typeContainer;
    }

    public function getQuery() : \Graphpinator\Type\Type
    {
        return $this->query;
    }

    public function getMutation() : ?\Graphpinator\Type\Type
    {
        return $this->mutation;
    }

    public function getSubscription() : ?\Graphpinator\Type\Type
    {
        return $this->subscription;
    }
}
