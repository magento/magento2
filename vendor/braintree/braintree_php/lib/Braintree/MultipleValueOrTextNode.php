<?php

class Braintree_MultipleValueOrTextNode extends Braintree_MultipleValueNode
{
    function __construct($name)
    {
        parent::__construct($name);
        $this->textNode = new Braintree_TextNode($name);
    }

    function contains($value)
    {
        $this->textNode->contains($value);
        return $this;
    }

    function endsWith($value)
    {
        $this->textNode->endsWith($value);
        return $this;
    }

    function is($value)
    {
        $this->textNode->is($value);
        return $this;
    }

    function isNot($value)
    {
        $this->textNode->isNot($value);
        return $this;
    }

    function startsWith($value)
    {
        $this->textNode->startsWith($value);
        return $this;
    }

    function toParam()
    {
        return array_merge(parent::toParam(), $this->textNode->toParam());
    }
}
