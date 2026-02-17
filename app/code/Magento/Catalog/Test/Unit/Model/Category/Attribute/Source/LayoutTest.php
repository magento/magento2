<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
    /**
     * @var array
     */
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
        $mockPageLayoutConfig = $this->createMock(Config::class);
        $mockPageLayoutConfig->method('toOptionArray')->willReturn($this->testArray);

        $mockPageLayoutBuilder = $this->createMock(BuilderInterface::class);
        $mockPageLayoutBuilder->expects($this->once())
            ->method('getPageLayoutsConfig')
            ->willReturn($mockPageLayoutConfig);

        return $mockPageLayoutBuilder;
    }
}
