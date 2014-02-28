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

namespace Magento\View\Publisher;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class FileTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\View\Publisher\File */
    protected $file;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\App\Filesystem|\PHPUnit_Framework_MockObject_MockObject */
    protected $filesystemMock;

    /** @var \Magento\View\Service|\PHPUnit_Framework_MockObject_MockObject */
    protected $serviceMock;

    /** @var \Magento\Module\Dir\Reader|\PHPUnit_Framework_MockObject_MockObject */
    protected $readerMock;

    /** @var \Magento\View\FileSystem|\PHPUnit_Framework_MockObject_MockObject */
    protected $viewFileSystem;

    /**
     * @var \Magento\Filesystem\Directory\WriteInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $rootDirectory;

    /**
     * @var string
     */
    protected $libDir = '/some/pub/lib/dir';

    /**
     * @var string
     */
    protected $viewStaticDir = '/some/view/static/dir';

    /**
     * @var string
     */
    protected $themeDir = '/some/theme/dir';

    /**
     * @param string $filePath
     * @param bool $allowDuplication
     * @param array $viewParams
     * @param null|string $sourcePath
     */
    protected function getModelMock($filePath, $allowDuplication, $viewParams, $sourcePath = null)
    {
        $this->rootDirectory = $this->getMock('Magento\Filesystem\Directory\WriteInterface');

        $this->filesystemMock = $this->getMock('Magento\App\Filesystem', [], [], '', false);
        $this->filesystemMock->expects($this->once())
            ->method('getDirectoryWrite')
            ->with($this->equalTo(\Magento\App\Filesystem::ROOT_DIR))
            ->will($this->returnValue($this->rootDirectory));
        $this->filesystemMock->expects($this->any())
            ->method('getPath')
            ->with($this->anything())
            ->will($this->returnCallback(array($this, 'getPathCallback')));
        $this->serviceMock = $this->getMock('Magento\View\Service', [], [], '', false);
        $this->readerMock = $this->getMock('Magento\Module\Dir\Reader', [], [], '', false);
        $this->viewFileSystem = $this->getMock('Magento\View\FileSystem', [], [], '', false);

        if ($sourcePath) {
            $this->rootDirectory->expects($this->any())
                ->method('getRelativePath')
                ->with($sourcePath)
                ->will($this->returnValue('related\\' . $sourcePath));
            $this->rootDirectory->expects($this->any())
                ->method('isExist')
                ->with('related\\' . $sourcePath)
                ->will($this->returnValue(true));
        }

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->file = $this->objectManagerHelper->getObject(
            'Magento\View\Publisher\File',
            [
                'filesystem' => $this->filesystemMock,
                'viewService' => $this->serviceMock,
                'modulesReader' => $this->readerMock,
                'viewFileSystem' => $this->viewFileSystem,
                'filePath' => $filePath,
                'allowDuplication' => $allowDuplication,
                'viewParams' => $viewParams,
                'sourcePath' => $sourcePath
            ]
        );
    }

    /**
     * @param string $param
     * @return string
     * @throws \UnexpectedValueException
     */
    public function getPathCallback($param)
    {
        switch ($param) {
            case \Magento\App\Filesystem::PUB_LIB_DIR:
                return $this->libDir;
            case \Magento\App\Filesystem::STATIC_VIEW_DIR:
                return $this->viewStaticDir;
            case \Magento\App\Filesystem::THEMES_DIR:
                return $this->themeDir;
            default:
                throw new \UnexpectedValueException('Path callback received wrong value: ' . $param);
        }
    }

    /**
     * @param null|string $sourcePath
     * @param bool $expected
     * @internal param null|string $sourceFile
     * @dataProvider isPublicationAllowedDataProvider
     */
    public function testIsPublicationAllowed($sourcePath, $expected)
    {
        $filePath = 'some/file/path';
        $this->getModelMock($filePath, true, ['some', 'array'], $sourcePath);

        $this->assertSame($expected, $this->file->isPublicationAllowed());
    }

    /**
     * @return array
     */
    public function isPublicationAllowedDataProvider()
    {
        return [
            [null, true],
            ['some/interesting/path/to/file', true],
            ['some\interesting\path\to\file', true],
            [$this->libDir . '/path/to/file', false],
            [$this->libDir . '\path\to\file', false],
            [$this->viewStaticDir . '\path\to\file', false],
            [$this->viewStaticDir . '/path/to/file', false],
            [$this->themeDir . '/path/to/file', true],
            [$this->themeDir . '\path\to\file', true],
        ];
    }

    /**
     * @param string $filePath
     * @param bool $allowDuplication
     * @param array $viewParams
     * @param string|null $sourcePath
     * @param string $expected
     * @dataProvider buildUniquePathDataProvider
     */
    public function testBuildUniquePath($filePath, $allowDuplication, $viewParams, $sourcePath, $expected)
    {
        $this->getModelMock($filePath, $allowDuplication, $viewParams, $sourcePath);
        if (!$allowDuplication && isset($viewParams['module'])) {
            $this->readerMock->expects($this->once())
                ->method('getModuleDir')
                ->with($this->equalTo('theme'), $this->equalTo($viewParams['module']))
                ->will($this->returnValue('custom_module_dir'));
        }
        $this->assertSame($expected, $this->file->buildUniquePath());
    }

    /**
     * @return array
     */
    public function buildUniquePathDataProvider()
    {
        $themModelWithPath = $this->getMock('Magento\View\Design\ThemeInterface', [], [], '', false);
        $themModelWithPath->expects($this->any())->method('getThemePath')->will($this->returnValue('theme/path'));
        $themModelWithId = $this->getMock('Magento\View\Design\ThemeInterface', [], [], '', false);
        $themModelWithId->expects($this->any())->method('getId')->will($this->returnValue(11));
        return [
            'theme with path' => [
                'filePath' => 'some/file/path',
                'allowDuplication' => true,
                'viewParams' => [
                    'themeModel' => $themModelWithPath,
                    'area' => 'frontend',
                    'locale' => 'en_US',
                    'module' => 'some_module',
                ],
                'sourcePath' => null,
                'expected' => 'frontend/theme/path/en_US/some_module/some/file/path'
            ],
            'theme with id' => [
                'filePath' => 'some/file/path2',
                'allowDuplication' => true,
                'viewParams' => [
                    'themeModel' => $themModelWithId,
                    'area' => 'backend',
                    'locale' => 'en_EN',
                    'module' => 'some_other_module',
                ],
                'sourcePath' => null,
                'expected' => 'backend/_theme11/en_EN/some_other_module/some/file/path2'
            ],
            'theme without any data' => [
                'filePath' => 'some/file/path3',
                'allowDuplication' => true,
                'viewParams' => [
                    'themeModel' => $this->getMock('Magento\View\Design\ThemeInterface', [], [], '', false),
                    'locale' => 'fr_FR',
                    'area' => 'some_area',
                    'module' => null,
                ],
                'sourcePath' => null,
                'expected' => 'some_area/_view/fr_FR/some/file/path3'
            ],
            'no duplication modular file' => [
                'filePath' => 'some/file/path4',
                'allowDuplication' => false,
                'viewParams' => [
                    'themeModel' => $this->getMock('Magento\View\Design\ThemeInterface', [], [], '', false),
                    'locale' => 'fr_FR',
                    'area' => 'some_area',
                    'module' => 'My_Module',
                ],
                'sourcePath' => 'custom_module_dir/some/file/path2',
                'expected' => '_module/My_Module/some/file/path2'
            ],
            'no duplication theme file' => [
                'filePath' => 'some/file/path5',
                'allowDuplication' => false,
                'viewParams' => [
                    'themeModel' => $this->getMock('Magento\View\Design\ThemeInterface', [], [], '', false),
                    'locale' => 'fr_FR',
                    'area' => 'some_area'
                ],
                'sourcePath' => $this->themeDir . '/custom_module_dir/some/file/path5',
                'expected' => 'custom_module_dir/some/file/path5'
            ],
        ];
    }
}
