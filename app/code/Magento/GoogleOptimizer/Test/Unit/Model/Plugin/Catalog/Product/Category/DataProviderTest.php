<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
        $this->helper = $this->getMockBuilder(\Magento\GoogleOptimizer\Helper\Data::class)
            ->setMethods(['isGoogleExperimentActive'])
            ->disableOriginalConstructor()->getMock();
        $this->subject = $this->getMock(
            \Magento\Catalog\Ui\DataProvider\Product\Form\NewCategoryDataProvider::class,
            [],
            [],
            '',
            false
        );
        $this->plugin = $objectManager->getObject(
            \Magento\GoogleOptimizer\Model\Plugin\Catalog\Product\Category\DataProvider::class,
            [
                'helper' => $this->helper
            ]
        );
    }

    public function testAfterGetMetaPositive()
    {
        $this->helper->expects($this->any())->method('isGoogleExperimentActive')->willReturn(true);
        $result = $this->plugin->afterGetMeta($this->subject, []);

        $children = $result['data']['children'];
        $this->assertArrayHasKey('experiment_script', $children);
        $this->assertFalse($children['experiment_script']['arguments']['data']['config']['componentDisabled']);
        $this->assertArrayHasKey('code_id', $children);
        $this->assertFalse($children['code_id']['arguments']['data']['config']['componentDisabled']);
    }

    public function testAfterGetMetaNegative()
    {
        $this->helper->expects($this->any())->method('isGoogleExperimentActive')->willReturn(false);
        $result = $this->plugin->afterGetMeta($this->subject, []);

        $children = $result['data']['children'];
        $this->assertArrayHasKey('experiment_script', $children);
        $this->assertTrue($children['experiment_script']['arguments']['data']['config']['componentDisabled']);
        $this->assertArrayHasKey('code_id', $children);
        $this->assertTrue($children['code_id']['arguments']['data']['config']['componentDisabled']);
    }
}
