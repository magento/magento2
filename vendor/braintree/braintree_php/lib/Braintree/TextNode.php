<?php

class Braintree_TextNode extends Braintree_PartialMatchNode
{
    function contains($value)
    {
        $this->searchTerms["contains"] = strval($value);
        return $this;
    }
}
