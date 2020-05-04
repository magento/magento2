<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\File\FileList;

use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\File;
use Magento\Framework\View\File\FileList\Collator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CollatorTest extends TestCase
{
    /**
     * @var Collator
     */
    protected $_model;

    /**
     * @var File[]
     */
    protected $_originFiles;

    /**
     * @var File
     */
    protected $_baseFile;

    /**
     * @var File
     */
    protected $_themeFile;

    protected function setUp(): void
    {
        $this->_baseFile = $this->_createViewFile('fixture.xml', 'Fixture_TestModule');
        $this->_themeFile = $this->_createViewFile('fixture.xml', 'Fixture_TestModule', 'area/theme/path');
        $this->_originFiles = [
            $this->_baseFile->getFileIdentifier() => $this->_baseFile,
            $this->_themeFile->getFileIdentifier() => $this->_themeFile,
        ];
        $this->_model = new Collator();
    }

    /**
     * Return newly created theme view file with a mocked theme
     *
     * @param string $filename
     * @param string $module
     * @param string|null $themeFullPath
     * @return MockObject|File
     */
    protected function _createViewFile($filename, $module, $themeFullPath = null)
    {
        $theme = null;
        if ($themeFullPath !== null) {
            $theme = $this->getMockForAbstractClass(ThemeInterface::class);
            $theme->expects($this->any())->method('getFullPath')->willReturn($themeFullPath);
        }
        return new File($filename, $module, $theme);
    }

    public function testCollateBaseFile()
    {
        $file = $this->_createViewFile('test/fixture.xml', 'Fixture_TestModule');
        $this->assertSame(
            [$file->getFileIdentifier() => $file, $this->_themeFile->getFileIdentifier() => $this->_themeFile],
            $this->_model->collate([$file], $this->_originFiles)
        );
    }

    public function testReplaceThemeFile()
    {
        $file = $this->_createViewFile('test/fixture.xml', 'Fixture_TestModule', 'area/theme/path');
        $this->assertSame(
            [$this->_baseFile->getFileIdentifier() => $this->_baseFile, $file->getFileIdentifier() => $file],
            $this->_model->collate([$file], $this->_originFiles)
        );
    }

    public function testReplaceBaseFileException()
    {
        $this->expectException('LogicException');
        $this->expectExceptionMessage('Overriding view file \'new.xml\' does not match to any of the files');
        $file = $this->_createViewFile('new.xml', 'Fixture_TestModule');
        $this->_model->collate([$file], $this->_originFiles);
    }

    public function testReplaceBaseFileEmptyThemePathException()
    {
        $this->expectException('LogicException');
        $this->expectExceptionMessage('Overriding view file \'test/fixture.xml\' does not match to any of the files');
        $file = $this->_createViewFile('test/fixture.xml', 'Fixture_TestModule', '');
        $this->_model->collate([$file], $this->_originFiles);
    }

    public function testReplaceThemeFileException()
    {
        $this->expectException('LogicException');
        $this->expectExceptionMessage('Overriding view file \'new.xml\' does not match to any of the files');
        $file = $this->_createViewFile('new.xml', 'Fixture_TestModule', 'area/theme/path');
        $this->_model->collate([$file], $this->_originFiles);
    }
}
