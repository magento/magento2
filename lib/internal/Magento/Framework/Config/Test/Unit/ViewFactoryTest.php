<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config\Test\Unit;

class ViewFactoryTest extends \PHPUnit_Framework_TestCase
{
    const AREA = 'frontend';

    /**
     * @var \Magento\Framework\Config\ViewFactory
     */
    protected $model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\View\Design\ThemeInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $theme;

    /**
     * @var \Magento\Framework\Config\View|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $view;

    protected function setUp()
    {
        $this->objectManager = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $this->model = new \Magento\Framework\Config\ViewFactory($this->objectManager);
        $this->theme = $this->getMock('Magento\Framework\View\Design\ThemeInterface');
        $this->view = $this->getMock('Magento\Framework\Config\View', [], [], '', false);
    }

    public function testCreate()
    {
        $this->objectManager->expects($this->once())
            ->method('create')
            ->with('Magento\Framework\Config\View', [])
            ->willReturn($this->view);
        $this->assertEquals($this->view, $this->model->create());
    }

    public function testCreateWithArguments()
    {
        /** @var \Magento\Theme\Model\View\Design|\PHPUnit_Framework_MockObject_MockObject $design */
        $design = $this->getMock('Magento\Theme\Model\View\Design', [], [], '', false);
        $design->expects($this->once())
            ->method('setDesignTheme')
            ->with($this->theme, self::AREA);

        /** @var \Magento\Framework\Config\FileResolver|\PHPUnit_Framework_MockObject_MockObject $fileResolver */
        $fileResolver = $this->getMock('Magento\Framework\Config\FileResolver', [], [], '', false);

        $valueMap = [
            ['Magento\Theme\Model\View\Design', [], $design],
            ['Magento\Framework\Config\FileResolver', ['designInterface' => $design], $fileResolver],
            ['Magento\Framework\Config\View', ['fileResolver' => $fileResolver], $this->view],
        ];
        $this->objectManager->expects($this->exactly(3))
            ->method('create')
            ->willReturnMap($valueMap);

        $this->assertEquals($this->view, $this->model->create($this->getArguments()));
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage wrong theme doesn't implement ThemeInterface
     */
    public function testCreateException()
    {
        $this->model->create([
            'themeModel' => 'wrong theme',
            'area' => self::AREA
        ]);
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
