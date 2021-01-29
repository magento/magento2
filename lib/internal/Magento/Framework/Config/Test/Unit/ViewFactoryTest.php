<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config\Test\Unit;

class ViewFactoryTest extends \PHPUnit\Framework\TestCase
{
    const AREA = 'frontend';

    /**
     * @var \Magento\Framework\Config\ViewFactory
     */
    protected $model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\View\Design\ThemeInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $theme;

    /**
     * @var \Magento\Framework\Config\View|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $view;

    protected function setUp(): void
    {
        $this->objectManager = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->model = new \Magento\Framework\Config\ViewFactory($this->objectManager);
        $this->theme = $this->createMock(\Magento\Framework\View\Design\ThemeInterface::class);
        $this->view = $this->createMock(\Magento\Framework\Config\View::class);
    }

    public function testCreate()
    {
        $this->objectManager->expects($this->once())
            ->method('create')
            ->with(\Magento\Framework\Config\View::class, [])
            ->willReturn($this->view);
        $this->assertEquals($this->view, $this->model->create());
    }

    public function testCreateWithArguments()
    {
        /** @var \Magento\Theme\Model\View\Design|\PHPUnit\Framework\MockObject\MockObject $design */
        $design = $this->createMock(\Magento\Theme\Model\View\Design::class);
        $design->expects($this->once())
            ->method('setDesignTheme')
            ->with($this->theme, self::AREA);

        /** @var \Magento\Framework\Config\FileResolver|\PHPUnit\Framework\MockObject\MockObject $fileResolver */
        $fileResolver = $this->createMock(\Magento\Framework\Config\FileResolver::class);

        $valueMap = [
            [\Magento\Theme\Model\View\Design::class, [], $design],
            [\Magento\Framework\Config\FileResolver::class, ['designInterface' => $design], $fileResolver],
            [\Magento\Framework\Config\View::class, ['fileResolver' => $fileResolver], $this->view],
        ];
        $this->objectManager->expects($this->exactly(3))
            ->method('create')
            ->willReturnMap($valueMap);

        $this->assertEquals($this->view, $this->model->create($this->getArguments()));
    }

    /**
     */
    public function testCreateException()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
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
