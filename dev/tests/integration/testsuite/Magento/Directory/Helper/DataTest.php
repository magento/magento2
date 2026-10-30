<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Directory\Helper;

class DataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $helper;

    protected function setUp(): void
    {
        $this->helper = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Directory\Helper\Data::class
        );
    }

    public function testGetDefaultCountry()
    {
        $this->assertEquals('US', $this->helper->getDefaultCountry());
    }
}
