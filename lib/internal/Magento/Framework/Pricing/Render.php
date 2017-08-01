<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Pricing;

use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\Render\Layout;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Template;

/**
 * Base price render
 *
 * @api
 * @method string getPriceRenderHandle()
 *
 * @api
 * @since 2.0.0
 */
class Render extends AbstractBlock
{
    /**#@+
     * Zones where prices displaying can be configured
     */
    const ZONE_ITEM_VIEW = 'item_view';
    const ZONE_ITEM_LIST = 'item_list';
    const ZONE_ITEM_OPTION = 'item_option';
    const ZONE_SALES     = 'sales';
    const ZONE_EMAIL     = 'email';
    const ZONE_CART      = 'cart';
    const ZONE_DEFAULT   = null;

    /**#@-*/


    /**
     * Default type renderer
     *
     * @var string
     * @since 2.0.0
     */
    protected $defaultTypeRender = 'default';

    /**
     * Price layout
     *
     * @var Layout
     * @since 2.0.0
     */
    protected $priceLayout;

    /**
     * Constructor
     *
     * @param Template\Context $context
     * @param Layout $priceLayout
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        Template\Context $context,
        Layout $priceLayout,
        array $data = []
    ) {
        $this->priceLayout = $priceLayout;
        parent::__construct($context, $data);
    }

    /**
     * Prepare layout
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _prepareLayout()
    {
        $this->priceLayout->addHandle($this->getPriceRenderHandle());
        $this->priceLayout->loadLayout();
        return parent::_prepareLayout();
    }

    /**
     * Render price
     *
     * @param string $priceCode
     * @param SaleableInterface $saleableItem
     * @param array $arguments
     * @return string
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @since 2.0.0
     */
    public function render($priceCode, SaleableInterface $saleableItem, array $arguments = [])
    {
        $useArguments = array_replace($this->_data, $arguments);

        /** @var \Magento\Framework\Pricing\Render\RendererPool $rendererPool */
        $rendererPool = $this->priceLayout->getBlock('render.product.prices');
        if (!$rendererPool) {
            throw new \RuntimeException('Wrong Price Rendering layout configuration. Factory block is missed');
        }

        // obtain concrete Price Render
        $priceRender = $rendererPool->createPriceRender($priceCode, $saleableItem, $useArguments);
        return $priceRender->toHtml();
    }

    /**
     * Render price amount
     *
     * @param AmountInterface $amount
     * @param PriceInterface $price
     * @param SaleableInterface $saleableItem
     * @param array $arguments
     * @return string
     * @throws \RuntimeException
     * @since 2.0.0
     */
    public function renderAmount(
        AmountInterface $amount,
        PriceInterface $price,
        SaleableInterface $saleableItem = null,
        array $arguments = []
    ) {
        $useArguments = array_replace($this->_data, $arguments);

        /** @var \Magento\Framework\Pricing\Render\RendererPool $rendererPool */
        $rendererPool = $this->priceLayout->getBlock('render.product.prices');
        if (!$rendererPool) {
            throw new \RuntimeException('Wrong Price Rendering layout configuration. Factory block is missed');
        }

        // obtain concrete Amount Render
        $amountRender = $rendererPool->createAmountRender($amount, $saleableItem, $price, $useArguments);
        return $amountRender->toHtml();
    }
}
