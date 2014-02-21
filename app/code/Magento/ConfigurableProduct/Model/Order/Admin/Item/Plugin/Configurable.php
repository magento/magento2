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

namespace Magento\ConfigurableProduct\Model\Order\Admin\Item\Plugin;

class Configurable
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     */
    public  function __construct(\Magento\Catalog\Model\ProductFactory $productFactory)
    {
        $this->productFactory = $productFactory;
    }

    /**
     * Get item sku
     *
     * @param array $arguments
     * @param \Magento\Code\Plugin\InvocationChain $invocationChain
     *
     * @return string
     */
    public function aroundGetSku(array $arguments, \Magento\Code\Plugin\InvocationChain $invocationChain)
    {
        /** @var \Magento\Sales\Model\Order\Item $item */
        list($item) = $arguments;

        if ($item->getProductType() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            $productOptions = $item->getProductOptions();
            return $productOptions['simple_sku'];
        }

        return $invocationChain->proceed($arguments);
    }

    /**
     * Get item name
     *
     * @param array $arguments
     * @param \Magento\Code\Plugin\InvocationChain $invocationChain
     *
     * @return string
     */
    public function aroundGetName(array $arguments, \Magento\Code\Plugin\InvocationChain $invocationChain)
    {
        /** @var \Magento\Sales\Model\Order\Item $item */
        list($item) = $arguments;

        if ($item->getProductType() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            $productOptions = $item->getProductOptions();
            return $productOptions['simple_name'];
        }

        return $invocationChain->proceed($arguments);
    }

    /**
     * Get product id
     *
     * @param array $arguments
     * @param \Magento\Code\Plugin\InvocationChain $invocationChain
     *
     * @return int
     */
    public function aroundGetProductId(array $arguments, \Magento\Code\Plugin\InvocationChain $invocationChain)
    {
        /** @var \Magento\Sales\Model\Order\Item $item */
        list($item) = $arguments;

        if ($item->getProductType() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            $productOptions     = $item->getProductOptions();
            $product = $this->productFactory->create();
            return $product->getIdBySku($productOptions['simple_sku']);
        }
        return $invocationChain->proceed($arguments);
    }
}
