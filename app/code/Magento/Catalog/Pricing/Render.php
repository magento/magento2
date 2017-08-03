<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
 * @api
 * @method string getPriceRender()
 * @method string getPriceTypeCode()
 * @since 2.0.0
 */
class Render extends Template
{
    /**
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $registry;

    /**
     * Construct
     *
     * @param Template\Context $context
     * @param Registry $registry
     * @param array $data
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
