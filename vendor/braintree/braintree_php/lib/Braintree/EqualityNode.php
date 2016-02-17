<?php

class Braintree_EqualityNode extends Braintree_IsNode
{
    function isNot($value)
    {
        $this->searchTerms['is_not'] = strval($value);
        return $this;
    }
}
