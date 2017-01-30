<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Model\Product;

/**
 * Integration test for Magento\Bundle\Model\OptionList
 */
class OptionListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $product;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * @magentoDataFixture Magento/Bundle/_files/product.php
     */
    public function testGetItems()
    {
        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->create('Magento\Catalog\Api\ProductRepositoryInterface');
        $this->product = $productRepository->get('bundle-product');
        /**
         * @var \Magento\Bundle\Model\Product\OptionList $optionList
         */
        $optionList = $this->objectManager->create('\Magento\Bundle\Model\Product\OptionList');
        $options = $optionList->getItems($this->product);
        $this->assertEquals(1, count($options));
        $this->assertEquals('Bundle Product Items', $options[0]->getTitle());
    }
}
