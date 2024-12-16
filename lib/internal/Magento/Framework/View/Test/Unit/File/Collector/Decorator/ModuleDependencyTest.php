<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\File\Collector\Decorator;

use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\File;
use Magento\Framework\View\File\Collector\Decorator\ModuleDependency;
use Magento\Framework\View\File\CollectorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ModuleDependencyTest extends TestCase
{
    /**
     * @var ModuleDependency
     */
    private $_model;

    /**
     * @var MockObject
     */
    private $_fileSource;

    /**
     * @var MockObject
     */
    private $_moduleListMock;

    protected function setUp(): void
    {
        $this->_fileSource = $this->getMockForAbstractClass(CollectorInterface::class);
        $this->_moduleListMock = $this->getMockForAbstractClass(ModuleListInterface::class);
        $this->_moduleListMock->expects($this->any())
            ->method('getNames')
            ->willReturn(['Fixture_ModuleB', 'Fixture_ModuleA']);
        $this->_model = new ModuleDependency(
            $this->_fileSource,
            $this->_moduleListMock
        );
    }

    /**
     * @param array $fixtureFiles
     * @param array $expectedFiles
     * @param string $message
     * @dataProvider getFilesDataProvider
     */
    public function testGetFiles(array $fixtureFiles, array $expectedFiles, $message)
    {
        $theme = $this->getMockForAbstractClass(ThemeInterface::class);
        $this->_fileSource
            ->expects($this->once())
            ->method('getFiles')
            ->with($theme, '*.xml')
            ->willReturn($fixtureFiles);
        $this->assertSame($expectedFiles, $this->_model->getFiles($theme, '*.xml'), $message);
    }

    /**
     * @return array
     */
    public static function getFilesDataProvider()
    {
        $fileOne = new File('b.xml', 'Fixture_ModuleB');
        $fileTwo = new File('a.xml', 'Fixture_ModuleA');
        $fileThree = new File('b.xml', 'Fixture_ModuleA');

        $unknownFileOne = new File('b.xml', 'Unknown_ModuleA');
        $unknownFileTwo = new File('a.xml', 'Unknown_ModuleB');
        return [
            'same module' => [
                [$fileThree, $fileTwo],
                [$fileTwo, $fileThree],
                'Files belonging to the same module are expected to be sorted by file names',
            ],
            'different modules' => [
                [$fileTwo, $fileOne],
                [$fileOne, $fileTwo],
                'Files belonging to different modules are expected to be sorted by module dependencies',
            ],
            'different unknown modules' => [
                [$unknownFileTwo, $unknownFileOne],
                [$unknownFileOne, $unknownFileTwo],
                'Files belonging to different unknown modules are expected to be sorted by module names',
            ],
            'known and unknown modules' => [
                [$fileTwo, $unknownFileOne],
                [$unknownFileOne, $fileTwo],
                'Files belonging to unknown modules are expected to go before ones of known modules',
            ],
        ];
    }
}
