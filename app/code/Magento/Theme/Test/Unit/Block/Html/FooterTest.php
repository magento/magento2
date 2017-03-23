<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Block\Html;

class FooterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Theme\Block\Html\Footer
     */
    protected $block;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->block = $objectManager->getObject(\Magento\Theme\Block\Html\Footer::class);
    }

    protected function tearDown()
    {
        $this->block = null;
    }

    public function testGetIdentities()
    {
        $this->assertEquals(
            [\Magento\Store\Model\Store::CACHE_TAG, \Magento\Cms\Model\Block::CACHE_TAG],
            $this->block->getIdentities()
        );
    }
}
