<?php

declare(strict_types = 1);

namespace Graphpinator\Tokenizer;

final class TokenContainer implements \IteratorAggregate
{
    use \Nette\SmartObject;

    private array $tokens = [];
    private int $currentIndex = 0;

    public function __construct(string $source, bool $skipNotRelevant = true)
    {
        $tokenizer = new \Graphpinator\Tokenizer\Tokenizer($source, $skipNotRelevant);

        foreach ($tokenizer as $token) {
            $this->tokens[] = $token;
        }
    }

    public function hasPrev() : bool
    {
        return \array_key_exists($this->currentIndex - 1, $this->tokens);
    }

    public function hasNext() : bool
    {
        return \array_key_exists($this->currentIndex + 1, $this->tokens);
    }

    public function isEmpty() : bool
    {
        return \count($this->tokens) === 0;
    }

    public function getCurrent() : Token
    {
        return $this->tokens[$this->currentIndex];
    }

    public function getPrev() : Token
    {
        if (!$this->hasPrev()) {
            throw new \Exception('Unexpected end.');
        }

        return $this->tokens[--$this->currentIndex];
    }

    public function getNext() : Token
    {
        if (!$this->hasNext()) {
            throw new \Exception('Unexpected end.');
        }

        return $this->tokens[++$this->currentIndex];
    }

    public function peekNext() : Token
    {
        if (!$this->hasNext()) {
            throw new \Exception('Unexpected end.');
        }

        return $this->tokens[$this->currentIndex + 1];
    }

    public function assertNext(string $tokenType) : Token
    {
        $token = $this->getNext();

        if ($token->getType() !== $tokenType) {
            throw new \Exception('Unexpected token.');
        }

        return $token;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->tokens);
    }
}
