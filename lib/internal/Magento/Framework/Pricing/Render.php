<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\Pricing;

use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\Object\SaleableInterface;
use Magento\Framework\Pricing\Render\Layout;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\Pricing\Price\PriceInterface;

/**
 * Base price render
 *
 * @method string getPriceRenderHandle()
 */
class Render extends AbstractBlock
{
    /**@#+
     * Zones where prices displaying can be configured
     */
    const ZONE_ITEM_VIEW = 'item_view';
    const ZONE_ITEM_LIST = 'item_list';
    const ZONE_ITEM_OPTION = 'item_option';
    const ZONE_SALES     = 'sales';
    const ZONE_EMAIL     = 'email';
    const ZONE_CART      = 'cart';
    const ZONE_DEFAULT   = null;
    /**@#-*/

    /**
     * Default type renderer
     *
     * @var string
     */
    protected $defaultTypeRender = 'default';

    /**
     * Price layout
     *
     * @var Layout
     */
    protected $priceLayout;

    /**
     * Constructor
     *
     * @param Template\Context $context
     * @param Layout $priceLayout
     * @param array $data
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
