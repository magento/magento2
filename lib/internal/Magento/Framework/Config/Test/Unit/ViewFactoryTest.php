<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Config\Test\Unit;

use Magento\Framework\Config\FileResolver;
use Magento\Framework\Config\View;
use Magento\Framework\Config\ViewFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Theme\Model\View\Design;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ViewFactoryTest extends TestCase
{
    const AREA = 'frontend';

    /**
     * @var ViewFactory
     */
    protected $model;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\View\Design\ThemeInterface|MockObject
     */
    protected $theme;

    /**
     * @var View|MockObject
     */
    protected $view;

    protected function setUp(): void
    {
        $this->objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->model = new ViewFactory($this->objectManager);
        $this->theme = $this->getMockForAbstractClass(ThemeInterface::class);
        $this->view = $this->createMock(View::class);
    }

    public function testCreate()
    {
        $this->objectManager->expects($this->once())
            ->method('create')
            ->with(View::class, [])
            ->willReturn($this->view);
        $this->assertEquals($this->view, $this->model->create());
    }

    public function testCreateWithArguments()
    {
        /** @var Design|MockObject $design */
        $design = $this->createMock(Design::class);
        $design->expects($this->once())
            ->method('setDesignTheme')
            ->with($this->theme, self::AREA);

        /** @var FileResolver|MockObject $fileResolver */
        $fileResolver = $this->createMock(FileResolver::class);

        $valueMap = [
            [Design::class, [], $design],
            [FileResolver::class, ['designInterface' => $design], $fileResolver],
            [View::class, ['fileResolver' => $fileResolver], $this->view],
        ];
        $this->objectManager->expects($this->exactly(3))
            ->method('create')
            ->willReturnMap($valueMap);

        $this->assertEquals($this->view, $this->model->create($this->getArguments()));
    }

    public function testCreateException()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('wrong theme doesn\'t implement ThemeInterface');
        $this->model->create(
            [
                'themeModel' => 'wrong theme',
                'area' => self::AREA
            ]
        );
    }

    /**
     * @return array
     */
    protected function getArguments()
    {
        return [
            'themeModel' => $this->theme,
            'area'       => self::AREA
        ];
    }
}
