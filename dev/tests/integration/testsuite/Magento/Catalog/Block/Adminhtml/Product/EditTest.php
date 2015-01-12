<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Adminhtml\Product;

/**
 * @magentoAppArea adminhtml
 */
class EditTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Block\Adminhtml\Product\Edit
     */
    protected $_block;

    protected function setUp()
    {
        parent::setUp();
        /** @var $product \Magento\Catalog\Model\Product */
        $product = $this->getMock(
            'Magento\Catalog\Model\Product',
            ['getAttributes', '__wakeup'],
            [],
            '',
            false
        );
        $product->expects($this->any())->method('getAttributes')->will($this->returnValue([]));
        $product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get('Magento\Framework\Registry')->register('current_product', $product);
        $this->_block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Catalog\Block\Adminhtml\Product\Edit'
        );
    }

    public function testGetTypeSwitcherData()
    {
        $data = json_decode($this->_block->getTypeSwitcherData(), true);
        $this->assertEquals('simple', $data['current_type']);
        $this->assertEquals([], $data['attributes']);
    }
}
