<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Block\Adminhtml;

/**
 * Test class for \Magento\Reports\Block\Adminhtml\Grid
 * @magentoAppArea adminhtml
 */
class GridTest extends \PHPUnit_Framework_TestCase
{
    public function testGetDateFormat()
    {
        /** @var $block \Magento\Reports\Block\Adminhtml\Grid */
        $block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Reports\Block\Adminhtml\Grid'
        );
        $this->assertNotEmpty($block->getDateFormat());
    }
}
