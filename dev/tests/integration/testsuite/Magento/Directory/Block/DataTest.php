<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Block;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Directory\Block\Data
     */
    private $block;

    /** @var \Magento\TestFramework\ObjectManager */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->block = $this->objectManager->get(\Magento\Directory\Block\Data::class);
    }

    public function testGetCountryHtmlSelect()
    {
        $this->cleanAllCache();
        $result = $this->block->getCountryHtmlSelect();
        $result2 = $this->block->getCountryHtmlSelect();
        $this->assertEquals($result, $result2);
    }

    public function testGetRegionHtmlSelect()
    {
        $this->cleanAllCache();
        $result = $this->block->getRegionHtmlSelect();
        $result2 = $this->block->getRegionHtmlSelect();
        $this->assertEquals($result, $result2);
    }

    private function cleanAllCache()
    {
        /** @var \Magento\Framework\App\Cache\Frontend\Pool $cachePool */
        $cachePool = $this->objectManager->get(\Magento\Framework\App\Cache\Frontend\Pool::class);
        /** @var \Magento\Framework\Cache\FrontendInterface $cacheType */
        foreach ($cachePool as $cacheType) {
            $cacheType->getBackend()->clean();
        }
    }
}
