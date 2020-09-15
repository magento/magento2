<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleOptimizer\Test\Unit\Model\Plugin\Catalog\Product\Category;

use Magento\Catalog\Ui\DataProvider\Product\Form\NewCategoryDataProvider;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\GoogleOptimizer\Helper\Data;
use Magento\GoogleOptimizer\Model\Plugin\Catalog\Product\Category\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DataProviderTest extends TestCase
{
    /**
     * @var DataProvider
     */
    private $plugin;

    /**
     * @var Data|MockObject
     */
    private $helper;

    /**
     * @var NewCategoryDataProvider
     */
    private $subject;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->helper = $this->getMockBuilder(Data::class)
            ->setMethods(['isGoogleExperimentActive'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->subject = $this->createMock(
            NewCategoryDataProvider::class
        );
        $this->plugin = $objectManager->getObject(
            DataProvider::class,
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
