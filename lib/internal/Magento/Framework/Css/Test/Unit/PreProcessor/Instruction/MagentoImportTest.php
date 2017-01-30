<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Framework\Css\Test\Unit\PreProcessor\Instruction;

use Magento\Framework\Css\PreProcessor\Instruction\MagentoImport;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Design\Theme\ThemeProviderInterface;

class MagentoImportTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\DesignInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $design;

    /**
     * @var \Magento\Framework\View\File\CollectorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fileSource;

    /**
     * @var \Magento\Framework\Css\PreProcessor\ErrorHandlerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $errorHandler;

    /**
     * @var \Magento\Framework\View\Asset\File|\PHPUnit_Framework_MockObject_MockObject
     */
    private $asset;

    /**
     * @var \Magento\Framework\View\Asset\Repository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $assetRepo;

    /**
     * @var \Magento\Framework\View\Design\Theme\ListInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $themeProvider;

    /**
     * @var \Magento\Framework\Css\PreProcessor\Instruction\Import
     */
    private $object;

    protected function setUp()
    {
        $this->design = $this->getMockForAbstractClass('\Magento\Framework\View\DesignInterface');
        $this->fileSource = $this->getMockForAbstractClass('\Magento\Framework\View\File\CollectorInterface');
        $this->errorHandler = $this->getMockForAbstractClass(
            '\Magento\Framework\Css\PreProcessor\ErrorHandlerInterface'
        );
        $this->asset = $this->getMock('\Magento\Framework\View\Asset\File', [], [], '', false);
        $this->asset->expects($this->any())->method('getContentType')->will($this->returnValue('css'));
        $this->assetRepo = $this->getMock('\Magento\Framework\View\Asset\Repository', [], [], '', false);
        $this->themeProvider = $this->getMock(ThemeProviderInterface::class);
        $this->object = (new ObjectManager($this))->getObject(MagentoImport::class, [
            'design' => $this->design,
            'fileSource' => $this->fileSource,
            'errorHandler' => $this->errorHandler,
            'assetRepo' => $this->assetRepo,
            'themeProvider' => $this->themeProvider
        ]);
    }

    /**
     * @param string $originalContent
     * @param string $foundPath
     * @param string $resolvedPath
     * @param array $foundFiles
     * @param string $expectedContent
     *
     * @dataProvider processDataProvider
     */
    public function testProcess($originalContent, $foundPath, $resolvedPath, $foundFiles, $expectedContent)
    {
        $chain = new \Magento\Framework\View\Asset\PreProcessor\Chain($this->asset, $originalContent, 'css', 'path');
        $relatedAsset = $this->getMock('\Magento\Framework\View\Asset\File', [], [], '', false);
        $relatedAsset->expects($this->once())
            ->method('getFilePath')
            ->will($this->returnValue($resolvedPath));
        $context = $this->getMock('\Magento\Framework\View\Asset\File\FallbackContext', [], [], '', false);
        $this->assetRepo->expects($this->once())
            ->method('createRelated')
            ->with($foundPath, $this->asset)
            ->will($this->returnValue($relatedAsset));
        $relatedAsset->expects($this->once())->method('getContext')->will($this->returnValue($context));
        $theme = $this->getMockForAbstractClass('\Magento\Framework\View\Design\ThemeInterface');
        $this->themeProvider->expects($this->once())->method('getThemeByFullPath')->will($this->returnValue($theme));
        $files = [];
        foreach ($foundFiles as $file) {
            $fileObject = $this->getMock('Magento\Framework\View\File', [], [], '', false);
            $fileObject->expects($this->any())
                ->method('getModule')
                ->will($this->returnValue($file['module']));
            $fileObject->expects($this->any())
                ->method('getFilename')
                ->will($this->returnValue($file['filename']));
            $files[] = $fileObject;
        }
        $this->fileSource->expects($this->once())
            ->method('getFiles')
            ->with($theme, $resolvedPath)
            ->will($this->returnValue($files));
        $this->object->process($chain);
        $this->assertEquals($expectedContent, $chain->getContent());
        $this->assertEquals('css', $chain->getContentType());
    }

    /**
     * @return array
     */
    public function processDataProvider()
    {
        return [
            'non-modular notation' => [
                '//@magento_import "some/file.css";',
                'some/file.css',
                'some/file.css',
                [
                    ['module' => null, 'filename' => 'some/file.css'],
                    ['module' => null, 'filename' => 'theme/some/file.css'],
                ],
                "@import 'some/file.css';\n@import 'some/file.css';\n",
            ],
            'modular' => [
                '//@magento_import "Magento_Module::some/file.css";',
                'Magento_Module::some/file.css',
                'some/file.css',
                [
                    ['module' => 'Magento_Module', 'filename' => 'some/file.css'],
                    ['module' => 'Magento_Two', 'filename' => 'some/file.css'],
                ],
                "@import 'Magento_Module::some/file.css';\n@import 'Magento_Two::some/file.css';\n",
            ],
            'non-modular reference notation' => [
                '//@magento_import (reference) "some/file.css";',
                'some/file.css',
                'some/file.css',
                [
                    ['module' => null, 'filename' => 'some/file.css'],
                    ['module' => null, 'filename' => 'theme/some/file.css'],
                ],
                "@import (reference) 'some/file.css';\n@import (reference) 'some/file.css';\n",
            ],
            'modular reference' => [
                '//@magento_import (reference) "Magento_Module::some/file.css";',
                'Magento_Module::some/file.css',
                'some/file.css',
                [
                    ['module' => 'Magento_Module', 'filename' => 'some/file.css'],
                    ['module' => 'Magento_Two', 'filename' => 'some/file.css'],
                ],
                "@import (reference) 'Magento_Module::some/file.css';\n" .
                "@import (reference) 'Magento_Two::some/file.css';\n",
            ],
        ];
    }

    public function testProcessNoImport()
    {
        $originalContent = 'color: #000000;';
        $expectedContent = 'color: #000000;';
        $chain = new \Magento\Framework\View\Asset\PreProcessor\Chain($this->asset, $originalContent, 'css', 'orig');
        $this->assetRepo->expects($this->never())
            ->method('createRelated');
        $this->object->process($chain);
        $this->assertEquals($expectedContent, $chain->getContent());
        $this->assertEquals('css', $chain->getContentType());
    }

    public function testProcessException()
    {
        $chain = new \Magento\Framework\View\Asset\PreProcessor\Chain(
            $this->asset, '//@magento_import "some/file.css";', 'css', 'path'
        );
        $exception = new \LogicException('Error happened');
        $this->assetRepo->expects($this->once())
            ->method('createRelated')
            ->will($this->throwException($exception));
        $this->errorHandler->expects($this->once())
            ->method('processException')
            ->with($exception);
        $this->object->process($chain);
        $this->assertEquals('', $chain->getContent());
        $this->assertEquals('css', $chain->getContentType());
    }
}
