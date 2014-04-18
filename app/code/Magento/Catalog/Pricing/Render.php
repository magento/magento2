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
 * @category    Magento
 * @package     Magento_Catalog
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Catalog\Pricing;

use Magento\Pricing\Object\SaleableInterface;
use Magento\View\Element\Template;
use Magento\Registry;
use Magento\Pricing\Render as PricingRender;

/**
 * Catalog Price Render
 *
 * @method string getPriceRender()
 * @method string getPriceTypeCode()
 * @method string getDisplayMsrpHelpMessage()
 */
class Render extends Template
{
    /**
     * @var \Magento\Registry
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
                return $priceRender->render($this->getPriceTypeCode(), $product, $this->getData());
            }
        }
        return parent::_toHtml();
    }

    /**
     * Returns saleable item instance
     *
     * @return SaleableInterface
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
