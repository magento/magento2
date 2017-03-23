<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Pricing;

use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\Pricing\Render as PricingRender;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;

/**
 * Catalog Price Render
 *
 * @method string getPriceRender()
 * @method string getPriceTypeCode()
 */
class Render extends Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * Construct
     *
     * @param Template\Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Produce and return block's html output
     *
     * @return string
     */
    protected function _toHtml()
    {
        /** @var PricingRender $priceRender */
        $priceRender = $this->getLayout()->getBlock($this->getPriceRender());
        if ($priceRender instanceof PricingRender) {
            $product = $this->getProduct();
            if ($product instanceof SaleableInterface) {
                $arguments = $this->getData();
                $arguments['render_block'] = $this;
                return $priceRender->render($this->getPriceTypeCode(), $product, $arguments);
            }
        }
        return parent::_toHtml();
    }

    /**
     * Returns saleable item instance
     *
     * @return Product
     */
    protected function getProduct()
    {
        $parentBlock = $this->getParentBlock();

        $product = $parentBlock && $parentBlock->getProductItem()
            ? $parentBlock->getProductItem()
            : $this->registry->registry('product');
        return $product;
    }
}
