<?php

class Braintree_PartialMatchNode extends Braintree_EqualityNode
{
    function startsWith($value)
    {
        $this->searchTerms["starts_with"] = strval($value);
        return $this;
    }

    function endsWith($value)
    {
        $this->searchTerms["ends_with"] = strval($value);
        return $this;
    }
}
