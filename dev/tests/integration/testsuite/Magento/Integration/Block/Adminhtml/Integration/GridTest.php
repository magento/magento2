<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
            ->create(\Magento\Integration\Block\Adminhtml\Integration\Grid::class);
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
