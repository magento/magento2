<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Pricing\Render;

use Magento\Catalog\Model\Product;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\Math\Random;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\Render\PriceBox as PriceBoxRender;
use Magento\Framework\Pricing\Render\RendererPool;
use Magento\Framework\View\Element\Template\Context;

/**
 * Default catalog price box render
 *
 * @method string getPriceElementIdPrefix()
 * @method string getIdSuffix()
 * @since 2.0.0
 */
class PriceBox extends PriceBoxRender
{
    /**
     * @var \Magento\Framework\Json\Helper\Data
     * @since 2.0.0
     */
    protected $jsonHelper;

    /**
     * @var \Magento\Framework\Math\Random
     * @since 2.0.0
     */
    protected $mathRandom;

    /**
     * @param Context $context
     * @param Product $saleableItem
     * @param PriceInterface $price
     * @param RendererPool $rendererPool
     * @param Data $jsonHelper
     * @param Random $mathRandom
     * @param array $data
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function __construct(
        Context $context,
        Product $saleableItem,
        PriceInterface $price,
        RendererPool $rendererPool,
        Data $jsonHelper,
        Random $mathRandom,
        array $data = []
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->mathRandom = $mathRandom;
        parent::__construct($context, $saleableItem, $price, $rendererPool);
    }

    /**
     * Encode the mixed $valueToEncode into the JSON format
     *
     * @param mixed $valueToEncode
     * @return string
     * @since 2.0.0
     */
    public function jsonEncode($valueToEncode)
    {
        return $this->jsonHelper->jsonEncode($valueToEncode);
    }

    /**
     * Get random string
     *
     * @param int $length
     * @param string|null $chars
     * @return string
     * @since 2.0.0
     */
    public function getRandomString($length, $chars = null)
    {
        return $this->mathRandom->getRandomString($length, $chars);
    }

    /**
     * Check if quantity can be displayed for tier price with msrp
     *
     * @param Product $product
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @since 2.0.0
     */
    public function getCanDisplayQty(Product $product)
    {
        //TODO Refactor - change to const similar to Model\Product\Type\Grouped::TYPE_CODE
        if ($product->getTypeId() == 'grouped') {
            return false;
        }
        return true;
    }
}
