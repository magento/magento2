<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Checkout\Model\Cart;

/**
 * Class RequestInfoFilterComposite
 * @api
 * @since 100.1.2
 */
class RequestInfoFilterComposite implements RequestInfoFilterInterface
{
    /**
     * @var RequestInfoFilter[] $params
     */
    private $filters = [];

    /**
     * @param RequestInfoFilter[] $filters
     */
    public function __construct(
        $filters = []
    ) {
        $this->filters = $filters;
    }

    /**
     * Loops through all leafs of the composite and calls filter method
     *
     * @param \Magento\Framework\DataObject $params
     * @return $this
     * @since 100.1.2
     */
    public function filter(\Magento\Framework\DataObject $params)
    {
        foreach ($this->filters as $filter) {
            $filter->filter($params);
        }
        return $this;
    }
}
