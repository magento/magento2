<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\File\Collector\Decorator;

use Magento\Framework\Module\Manager;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\File;
use Magento\Framework\View\File\Collector\Decorator\ModuleOutput;
use Magento\Framework\View\File\CollectorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ModuleOutputTest extends TestCase
{
    /**
     * @var ModuleOutput
     */
    private $_model;

    /**
     * @var MockObject
     */
    private $_fileSource;

    /**
     * @var MockObject
     */
    private $_moduleManager;

    protected function setUp(): void
    {
        $this->_fileSource = $this->getMockForAbstractClass(CollectorInterface::class);
        $this->_moduleManager = $this->createMock(Manager::class);
        $this->_moduleManager
            ->expects($this->any())
            ->method('isOutputEnabled')
            ->willReturnMap([
                ['Module_OutputEnabled', true],
                ['Module_OutputDisabled', false],
            ]);
        $this->_model = new ModuleOutput(
            $this->_fileSource,
            $this->_moduleManager
        );
    }

    public function testGetFiles()
    {
        $theme = $this->getMockForAbstractClass(ThemeInterface::class);
        $fileOne = new File('1.xml', 'Module_OutputEnabled');
        $fileTwo = new File('2.xml', 'Module_OutputDisabled');
        $fileThree = new File('3.xml', 'Module_OutputEnabled', $theme);
        $this->_fileSource
            ->expects($this->once())
            ->method('getFiles')
            ->with($theme, '*.xml')
            ->willReturn([$fileOne, $fileTwo, $fileThree]);
        $this->assertSame([$fileOne, $fileThree], $this->_model->getFiles($theme, '*.xml'));
    }
}
