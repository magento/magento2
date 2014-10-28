<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Less\File\FileList;

class CollatorTest extends \PHPUnit_Framework_TestCase
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

    protected function setUp()
    {
        $this->baseFile = $this->createLayoutFile('fixture_1.less', 'Fixture_TestModule');
        $this->themeFile = $this->createLayoutFile('fixture.less', 'Fixture_TestModule', 'area/theme/path');
        $this->originFiles = array(
            $this->baseFile->getFileIdentifier() => $this->baseFile,
            $this->themeFile->getFileIdentifier() => $this->themeFile
        );
        $this->model = new Collator();
    }

    /**
     * Return newly created theme layout file with a mocked theme
     *
     * @param string $filename
     * @param string $module
     * @param string|null $themeFullPath
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\File
     */
    protected function createLayoutFile($filename, $module, $themeFullPath = null)
    {
        $theme = null;
        if ($themeFullPath !== null) {
            $theme = $this->getMockForAbstractClass('Magento\Framework\View\Design\ThemeInterface');
            $theme->expects($this->any())->method('getFullPath')->will($this->returnValue($themeFullPath));
        }
        return new \Magento\Framework\View\File($filename, $module, $theme);
    }

    public function testCollate()
    {
        $file = $this->createLayoutFile('test/fixture.less', 'Fixture_TestModule');
        $expected = array(
            $this->baseFile->getFileIdentifier() => $this->baseFile,
            $file->getFileIdentifier() => $file
        );
        $result = $this->model->collate(array($file), $this->originFiles);
        $this->assertSame($expected, $result);
    }
}
