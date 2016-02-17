<?php

class Braintree_IsNode
{
    function __construct($name)
    {
        $this->name = $name;
        $this->searchTerms = array();
    }

    function is($value)
    {
        $this->searchTerms['is'] = strval($value);
        return $this;
    }

    function toParam()
    {
        return $this->searchTerms;
    }
}
