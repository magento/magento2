<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Dashboard;

/**
 * @magentoAppArea adminhtml
 */
class GraphTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Block\Dashboard\Graph
     */
    protected $_block;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Framework\View\LayoutInterface $layout */
        $layout = $objectManager->get('Magento\Framework\View\LayoutInterface');
        $this->_block = $layout->createBlock('Magento\Backend\Block\Dashboard\Graph');
        $this->_block->setDataHelper($objectManager->get('Magento\Backend\Helper\Dashboard\Order'));
    }

    /**
     * Tests getChartUrl.
     *
     * @return void
     */
    public function testGetChartUrl()
    {
        $this->assertStringStartsWith('https://image-charts.com/chart', $this->_block->getChartUrl());
    }
}
