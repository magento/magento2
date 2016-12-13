<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Block;

use Magento\TestFramework\Helper\CacheCleaner;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Directory\Block\Data
     */
    private $block;

    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->block = $objectManager->get(\Magento\Directory\Block\Data::class);
    }

    public function testGetCountryHtmlSelect()
    {
        CacheCleaner::cleanAll();
        $result = $this->block->getCountryHtmlSelect();
        $resultTwo = $this->block->getCountryHtmlSelect();
        $this->assertEquals($result, $resultTwo);
    }

    public function testGetRegionHtmlSelect()
    {
        CacheCleaner::cleanAll();
        $result = $this->block->getRegionHtmlSelect();
        $resultTwo = $this->block->getRegionHtmlSelect();
        $this->assertEquals($result, $resultTwo);
    }
}
