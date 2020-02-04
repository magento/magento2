<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Msrp\Pricing\Render;

use Magento\Catalog\Model\Product;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\Math\Random;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\Render\RendererPool;
use Magento\Framework\View\Element\Template\Context;
use Magento\Msrp\Pricing\MsrpPriceCalculatorInterface;

/**
 * MSRP price box render.
 */
class PriceBox extends \Magento\Catalog\Pricing\Render\PriceBox
{
    /**
     * @var MsrpPriceCalculatorInterface
     */
    private $msrpPriceCalculator;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Product $saleableItem
     * @param PriceInterface $price
     * @param RendererPool $rendererPool
     * @param Data $jsonHelper
     * @param Random $mathRandom
     * @param MsrpPriceCalculatorInterface $msrpPriceCalculator
     */
    public function __construct(
        Context $context,
        Product $saleableItem,
        PriceInterface $price,
        RendererPool $rendererPool,
        Data $jsonHelper,
        Random $mathRandom,
        MsrpPriceCalculatorInterface $msrpPriceCalculator
    ) {
        $this->msrpPriceCalculator = $msrpPriceCalculator;
        parent::__construct($context, $saleableItem, $price, $rendererPool, $jsonHelper, $mathRandom);
    }

    /**
     * Return MSRP price calculator.
     *
     * @return MsrpPriceCalculatorInterface
     */
    public function getMsrpPriceCalculator(): MsrpPriceCalculatorInterface
    {
        return $this->msrpPriceCalculator;
    }

    /**
     * @inheritDoc
     */
    public function getCacheKey()
    {
        return sprintf(
            '%s-%s',
            parent::getCacheKey(),
            $this->getZone()
        );
    }
}
