<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleOptimizer\Test\Unit\Model\Plugin\Catalog\Product\Category;

class DataProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\GoogleOptimizer\Model\Plugin\Catalog\Product\Category\DataProvider
     */
    private $plugin;

    /**
     * @var \Magento\GoogleOptimizer\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    private $helper;

    /**
     * @var \Magento\Catalog\Ui\DataProvider\Product\Form\NewCategoryDataProvider
     */
    private $subject;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->helper = $this->getMockBuilder('\Magento\GoogleOptimizer\Helper\Data')
            ->setMethods(['isGoogleExperimentActive'])
            ->disableOriginalConstructor()->getMock();
        $this->subject = $this->getMock(
            '\Magento\Catalog\Ui\DataProvider\Product\Form\NewCategoryDataProvider',
            [],
            [],
            '',
            false
        );
        $this->plugin = $objectManager->getObject(
            '\Magento\GoogleOptimizer\Model\Plugin\Catalog\Product\Category\DataProvider',
            [
                'helper' => $this->helper
            ]
        );
    }

    public function testAfterGetMetaPositive()
    {
        $this->helper->expects($this->any())->method('isGoogleExperimentActive')->willReturn(true);
        $result = $this->plugin->afterGetMeta($this->subject, []);

        $this->assertArrayHasKey('experiment_script', $result['data']['children']);
        $this->assertFalse($result['data']['children']['experiment_script']['componentDisabled']);
        $this->assertArrayHasKey('code_id', $result['data']['children']);
        $this->assertFalse($result['data']['children']['code_id']['componentDisabled']);
    }

    public function testAfterGetMetaNegative()
    {
        $this->helper->expects($this->any())->method('isGoogleExperimentActive')->willReturn(false);
        $result = $this->plugin->afterGetMeta($this->subject, []);

        $this->assertArrayHasKey('experiment_script', $result['data']['children']);
        $this->assertTrue($result['data']['children']['experiment_script']['componentDisabled']);
        $this->assertArrayHasKey('code_id', $result['data']['children']);
        $this->assertTrue($result['data']['children']['code_id']['componentDisabled']);
    }
}
