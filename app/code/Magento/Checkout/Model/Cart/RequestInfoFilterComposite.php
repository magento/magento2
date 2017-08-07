<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Model\Cart;

/**
 * Class RequestInfoFilterComposite
 * @api
 * @since 2.1.2
 */
class RequestInfoFilterComposite implements RequestInfoFilterInterface
{
    /**
     * @var RequestInfoFilter[] $params
     * @since 2.1.2
     */
    private $filters = [];

    /**
     * @param RequestInfoFilter[] $filters
     * @since 2.1.2
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
     * @since 2.1.2
     */
    public function filter(\Magento\Framework\DataObject $params)
    {
        foreach ($this->filters as $filter) {
            $filter->filter($params);
        }
        return $this;
    }
}
