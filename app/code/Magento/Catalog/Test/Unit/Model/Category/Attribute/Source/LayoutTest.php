<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Category\Attribute\Source;

use Magento\Catalog\Model\Category\Attribute\Source\Layout;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Model\PageLayout\Config\BuilderInterface;
use Magento\Framework\View\PageLayout\Config;
use PHPUnit\Framework\TestCase;

class LayoutTest extends TestCase
{
    private $testArray = ['test1', ['test1']];

    /**
     * @var Layout
     */
    private $model;

    public function testGetAllOptions()
    {
        $assertArray = $this->testArray;
        array_unshift($assertArray, ['value' => '', 'label' => __('No layout updates')]);
        $this->assertEquals($assertArray, $this->model->getAllOptions());
    }

    protected function setUp(): void
    {
        $helper = new ObjectManager($this);
        $this->model = $helper->getObject(
            Layout::class,
            [
                'pageLayoutBuilder' => $this->getMockedPageLayoutBuilder()
            ]
        );
    }

    /**
     * @return BuilderInterface
     */
    private function getMockedPageLayoutBuilder()
    {
        $mockPageLayoutConfig = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockPageLayoutConfig->expects($this->any())
            ->method('toOptionArray')
            ->willReturn($this->testArray);

        $mockPageLayoutBuilder = $this->getMockBuilder(
            BuilderInterface::class
        )->disableOriginalConstructor()
            ->getMock();
        $mockPageLayoutBuilder->expects($this->once())
            ->method('getPageLayoutsConfig')
            ->willReturn($mockPageLayoutConfig);

        return $mockPageLayoutBuilder;
    }
}
