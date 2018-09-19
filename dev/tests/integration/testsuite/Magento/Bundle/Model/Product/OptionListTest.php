<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Model\Product;

/**
 * Integration test for Magento\Bundle\Model\OptionList
 */
class OptionListTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $product;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * @magentoDataFixture Magento/Bundle/_files/product.php
     * @magentoDbIsolation disabled
     */
    public function testGetItems()
    {
        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->product = $productRepository->get('bundle-product');
        /**
         * @var \Magento\Bundle\Model\Product\OptionList $optionList
         */
        $optionList = $this->objectManager->create(\Magento\Bundle\Model\Product\OptionList::class);
        $options = $optionList->getItems($this->product);
        $this->assertEquals(1, count($options));
        $this->assertEquals('Bundle Product Items', $options[0]->getTitle());
    }
}
