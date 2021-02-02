<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Css\Test\Unit\PreProcessor\Instruction;

use Magento\Framework\Css\PreProcessor\Instruction\MagentoImport;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Design\Theme\ThemeProviderInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MagentoImportTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\View\DesignInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $design;

    /**
     * @var \Magento\Framework\View\File\CollectorInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $fileSource;

    /**
     * @var \Magento\Framework\Css\PreProcessor\ErrorHandlerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $errorHandler;

    /**
     * @var \Magento\Framework\View\Asset\File|\PHPUnit\Framework\MockObject\MockObject
     */
    private $asset;

    /**
     * @var \Magento\Framework\View\Asset\Repository|\PHPUnit\Framework\MockObject\MockObject
     */
    private $assetRepo;

    /**
     * @var ThemeProviderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $themeProvider;

    /**
     * @var \Magento\Framework\Css\PreProcessor\Instruction\Import
     */
    private $object;

    protected function setUp(): void
    {
        $this->design = $this->getMockForAbstractClass(\Magento\Framework\View\DesignInterface::class);
        $this->fileSource = $this->getMockForAbstractClass(\Magento\Framework\View\File\CollectorInterface::class);
        $this->errorHandler = $this->getMockForAbstractClass(
            \Magento\Framework\Css\PreProcessor\ErrorHandlerInterface::class
        );
        $this->asset = $this->createMock(\Magento\Framework\View\Asset\File::class);
        $this->asset->expects($this->any())->method('getContentType')->willReturn('css');
        $this->assetRepo = $this->createMock(\Magento\Framework\View\Asset\Repository::class);
        $this->themeProvider = $this->getMockForAbstractClass(ThemeProviderInterface::class);
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
        $relatedAsset = $this->createMock(\Magento\Framework\View\Asset\File::class);
        $relatedAsset->expects($this->once())
            ->method('getFilePath')
            ->willReturn($resolvedPath);
        $context = $this->createMock(\Magento\Framework\View\Asset\File\FallbackContext::class);
        $this->assetRepo->expects($this->once())
            ->method('createRelated')
            ->with($foundPath, $this->asset)
            ->willReturn($relatedAsset);
        $relatedAsset->expects($this->once())->method('getContext')->willReturn($context);
        $theme = $this->getMockForAbstractClass(\Magento\Framework\View\Design\ThemeInterface::class);
        $this->themeProvider->expects($this->once())->method('getThemeByFullPath')->willReturn($theme);
        $files = [];
        foreach ($foundFiles as $file) {
            $fileObject = $this->createMock(\Magento\Framework\View\File::class);
            $fileObject->expects($this->any())
                ->method('getModule')
                ->willReturn($file['module']);
            $fileObject->expects($this->any())
                ->method('getFilename')
                ->willReturn($file['filename']);
            $files[] = $fileObject;
        }
        $this->fileSource->expects($this->once())
            ->method('getFiles')
            ->with($theme, $resolvedPath)
            ->willReturn($files);
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
            $this->asset,
            '//@magento_import "some/file.css";',
            'css',
            'path'
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
