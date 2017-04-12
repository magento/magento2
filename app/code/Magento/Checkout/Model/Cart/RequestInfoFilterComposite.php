<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Model\Cart;

/**
 * Class RequestInfoFilterComposite
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
     */
    public function filter(\Magento\Framework\DataObject $params)
    {
        foreach ($this->filters as $filter) {
            $filter->filter($params);
        }
        return $this;
    }
}
