<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Css\Test\Unit\PreProcessor\File\FileList;

use \Magento\Framework\Css\PreProcessor\File\FileList\Collator;

class CollatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Collator
     */
    protected $model;

    /**
     * @var \Magento\Framework\View\File[]
     */
    protected $originFiles;

    /**
     * @var \Magento\Framework\View\File
     */
    protected $baseFile;

    /**
     * @var \Magento\Framework\View\File
     */
    protected $themeFile;

    protected function setUp(): void
    {
        $this->baseFile = $this->createLayoutFile('fixture_1.less', 'Fixture_TestModule');
        $this->themeFile = $this->createLayoutFile('fixture.less', 'Fixture_TestModule', 'area/theme/path');
        $this->originFiles = [
            $this->baseFile->getFileIdentifier() => $this->baseFile,
            $this->themeFile->getFileIdentifier() => $this->themeFile,
        ];
        $this->model = new Collator();
    }

    /**
     * Return newly created theme layout file with a mocked theme
     *
     * @param string $filename
     * @param string $module
     * @param string|null $themeFullPath
     * @return \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\View\File
     */
    protected function createLayoutFile($filename, $module, $themeFullPath = null)
    {
        $theme = null;
        if ($themeFullPath !== null) {
            $theme = $this->getMockForAbstractClass(\Magento\Framework\View\Design\ThemeInterface::class);
            $theme->expects($this->any())->method('getFullPath')->willReturn($themeFullPath);
        }
        return new \Magento\Framework\View\File($filename, $module, $theme);
    }

    public function testCollate()
    {
        $file = $this->createLayoutFile('test/fixture.less', 'Fixture_TestModule');
        $expected = [
            $this->baseFile->getFileIdentifier() => $this->baseFile,
            $file->getFileIdentifier() => $file,
        ];
        $result = $this->model->collate([$file], $this->originFiles);
        $this->assertSame($expected, $result);
    }
}
