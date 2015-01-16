<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\PageLayout;

/**
 * Page layouts configuration
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\PageLayout\Config
     */
    protected $config;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->config = $objectManagerHelper->getObject(
            'Magento\Framework\View\PageLayout\Config',
            [
                'configFiles' => [
                    'layouts_one.xml' => file_get_contents(__DIR__ . '/_files/layouts_one.xml'),
                    'layouts_two.xml' => file_get_contents(__DIR__ . '/_files/layouts_two.xml'),
                ]
            ]
        );
    }

    public function testGetPageLayouts()
    {
        $this->assertEquals(['one' => 'One', 'two' => 'Two'], $this->config->getPageLayouts());
    }

    public function testHasPageLayout()
    {
        $this->assertEquals(true, $this->config->hasPageLayout('one'));
        $this->assertEquals(false, $this->config->hasPageLayout('three'));
    }

    public function testGetOptions()
    {
        $this->assertEquals(['one' => 'One', 'two' => 'Two'], $this->config->getPageLayouts());
    }

    public function testToOptionArray()
    {
        $this->assertEquals(
            [
                ['label' => 'One', 'value' => 'one'],
                ['label' => 'Two', 'value' => 'two'],
            ],
            $this->config->toOptionArray()
        );
        $this->assertEquals(
            [
                ['label' => '-- Please Select --', 'value' => ''],
                ['label' => 'One', 'value' => 'one'],
                ['label' => 'Two', 'value' => 'two'],
            ],
            $this->config->toOptionArray(true)
        );
    }
}
