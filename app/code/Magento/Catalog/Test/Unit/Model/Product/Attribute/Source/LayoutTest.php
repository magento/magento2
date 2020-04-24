<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Attribute\Source;

use Magento\Catalog\Model\Product\Attribute\Source\Layout;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Model\PageLayout\Config\BuilderInterface;
use Magento\Framework\View\PageLayout\Config;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LayoutTest extends TestCase
{
    /** @var Layout */
    protected $layoutModel;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var BuilderInterface|MockObject */
    protected $pageLayoutBuilder;

    protected function setUp(): void
    {
        $this->pageLayoutBuilder = $this->getMockBuilder(
            BuilderInterface::class
        )->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->layoutModel = $this->objectManagerHelper->getObject(
            Layout::class,
            [
                'pageLayoutBuilder' => $this->pageLayoutBuilder
            ]
        );
    }

    public function testGetAllOptions()
    {
        $expectedOptions = [
            '0' => ['value' => '', 'label' => 'No layout updates'],
            '1' => ['value' => 'option_value', 'label' => 'option_label'],
        ];
        $mockPageLayoutConfig = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockPageLayoutConfig->expects($this->any())
            ->method('toOptionArray')
            ->willReturn(['0' => $expectedOptions['1']]);

        $this->pageLayoutBuilder->expects($this->once())
            ->method('getPageLayoutsConfig')
            ->willReturn($mockPageLayoutConfig);

        $layoutOptions = $this->layoutModel->getAllOptions();
        $this->assertEquals($expectedOptions, $layoutOptions);
    }
}
