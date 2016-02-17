<?php

class Braintree_RangeNode
{
    function __construct($name)
    {
        $this->name = $name;
        $this->searchTerms = array();
    }

    function greaterThanOrEqualTo($value)
    {
        $this->searchTerms['min'] = $value;
        return $this;
    }

    function lessThanOrEqualTo($value)
    {
        $this->searchTerms['max'] = $value;
        return $this;
    }

    function is($value)
    {
        $this->searchTerms['is'] = $value;
        return $this;
    }

    function between($min, $max)
    {
		return $this->greaterThanOrEqualTo($min)->lessThanOrEqualTo($max);
    }

    function toParam()
    {
        return $this->searchTerms;
    }
}
