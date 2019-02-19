<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Block\Adminhtml\Sales\Bestsellers;

/**
 * @magentoAppArea adminhtml
 */
class GridTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Reports\Block\Adminhtml\Sales\Bestsellers\Grid
     */
    protected $_block;

    protected function setUp()
    {
        parent::setUp();
        $this->_block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\LayoutInterface::class
        )->createBlock(
            \Magento\Reports\Block\Adminhtml\Sales\Bestsellers\Grid::class
        );
    }

    public function testGetResourceCollectionName()
    {
        $collectionName = $this->_block->getResourceCollectionName();
        $this->assertTrue(class_exists($collectionName));
    }
}
