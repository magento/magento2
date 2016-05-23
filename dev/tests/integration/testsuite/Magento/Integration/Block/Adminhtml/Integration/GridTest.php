<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace Magento\Integration\Block\Adminhtml\Integration;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test class for \Magento\Integration\Block\Adminhtml\Integration\Grid
 *
 * @magentoAppArea adminhtml
 */
class GridTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Integration\Block\Adminhtml\Integration\Grid
     */
    protected $gridBlock;

    protected function setUp()
    {
        $this->gridBlock = Bootstrap::getObjectManager()
            ->create('Magento\Integration\Block\Adminhtml\Integration\Grid');
    }

    public function testGetRowClickCallback()
    {
        $this->assertEquals('', $this->gridBlock->getRowClickCallback());
    }

    public function testGetRowInitCallback()
    {
        $this->assertEquals('', $this->gridBlock->getRowInitCallback());
    }
}
