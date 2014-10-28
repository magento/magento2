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
namespace Magento\Catalog\Pricing\Render;

use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\Render\PriceBox as PriceBoxRender;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Pricing\Render\RendererPool;
use Magento\Core\Helper\Data;
use Magento\Framework\Math\Random;

/**
 * Default catalog price box render
 *
 * @method string getPriceElementIdPrefix()
 * @method string getIdSuffix()
 */
class PriceBox extends PriceBoxRender
{
    /**
     * @var \Magento\Core\Helper\Data
     */
    protected $coreDataHelper;

    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $mathRandom;

    /**
     * @param Context $context
     * @param Product $saleableItem
     * @param PriceInterface $price
     * @param RendererPool $rendererPool
     * @param Data $coreDataHelper
     * @param Random $mathRandom
     * @param array $data
     */
    public function __construct(
        Context $context,
        Product $saleableItem,
        PriceInterface $price,
        RendererPool $rendererPool,
        Data $coreDataHelper,
        Random $mathRandom,
        array $data = array()
    ) {
        $this->coreDataHelper = $coreDataHelper;
        $this->mathRandom = $mathRandom;
        parent::__construct($context, $saleableItem, $price, $rendererPool);
    }

    /**
     * Encode the mixed $valueToEncode into the JSON format
     *
     * @param mixed $valueToEncode
     * @param boolean $cycleCheck Optional; whether or not to check for object recursion; off by default
     * @param array $options Additional options used during encoding
     * @return string
     */
    public function jsonEncode($valueToEncode, $cycleCheck = false, $options = [])
    {
        return $this->coreDataHelper->jsonEncode($valueToEncode, $cycleCheck, $options);
    }

    /**
     * Get random string
     *
     * @param int $length
     * @param string|null $chars
     * @return string
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
