<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
        $this->product = $this->objectManager->get('Magento\Catalog\Model\Product');
        $this->product->load(3);
        /**
         * @var \Magento\Bundle\Model\Product\OptionList $optionList
         */
        $optionList = $this->objectManager->create('\Magento\Bundle\Model\Product\OptionList');
        $options = $optionList->getItems($this->product);
        $this->assertEquals(1, count($options));
        $this->assertEquals('Bundle Product Items', $options[0]->getTitle());
    }
}
