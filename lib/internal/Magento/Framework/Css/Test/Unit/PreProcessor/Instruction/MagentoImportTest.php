<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Css\Test\Unit\PreProcessor\Instruction;

use Magento\Framework\Css\PreProcessor\ErrorHandlerInterface;
use Magento\Framework\Css\PreProcessor\Instruction\Import;
use Magento\Framework\Css\PreProcessor\Instruction\MagentoImport;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Asset\File;
use Magento\Framework\View\Asset\File\FallbackContext;
use Magento\Framework\View\Asset\PreProcessor\Chain;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Design\Theme\ThemeProviderInterface;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\File\CollectorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MagentoImportTest extends TestCase
{
    /**
     * @var DesignInterface|MockObject
     */
    private $design;

    /**
     * @var CollectorInterface|MockObject
     */
    private $fileSource;

    /**
     * @var ErrorHandlerInterface|MockObject
     */
    private $errorHandler;

    /**
     * @var File|MockObject
     */
    private $asset;

    /**
     * @var Repository|MockObject
     */
    private $assetRepo;

    /**
     * @var ThemeProviderInterface|MockObject
     */
    private $themeProvider;

    /**
     * @var Import
     */
    private $object;

    protected function setUp(): void
    {
        $this->design = $this->getMockForAbstractClass(DesignInterface::class);
        $this->fileSource = $this->getMockForAbstractClass(CollectorInterface::class);
        $this->errorHandler = $this->getMockForAbstractClass(
            ErrorHandlerInterface::class
        );
        $this->asset = $this->createMock(File::class);
        $this->asset->expects($this->any())->method('getContentType')->willReturn('css');
        $this->assetRepo = $this->createMock(Repository::class);
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
        $chain = new Chain($this->asset, $originalContent, 'css', 'path');
        $relatedAsset = $this->createMock(File::class);
        $relatedAsset->expects($this->once())
            ->method('getFilePath')
            ->willReturn($resolvedPath);
        $context = $this->createMock(FallbackContext::class);
        $this->assetRepo->expects($this->once())
            ->method('createRelated')
            ->with($foundPath, $this->asset)
            ->willReturn($relatedAsset);
        $relatedAsset->expects($this->once())->method('getContext')->willReturn($context);
        $theme = $this->getMockForAbstractClass(ThemeInterface::class);
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
        $chain = new Chain($this->asset, $originalContent, 'css', 'orig');
        $this->assetRepo->expects($this->never())
            ->method('createRelated');
        $this->object->process($chain);
        $this->assertEquals($expectedContent, $chain->getContent());
        $this->assertEquals('css', $chain->getContentType());
    }

    public function testProcessException()
    {
        $chain = new Chain(
            $this->asset,
            '//@magento_import "some/file.css";',
            'css',
            'path'
        );
        $exception = new \LogicException('Error happened');
        $this->assetRepo->expects($this->once())
            ->method('createRelated')
            ->willThrowException($exception);
        $this->errorHandler->expects($this->once())
            ->method('processException')
            ->with($exception);
        $this->object->process($chain);
        $this->assertEquals('', $chain->getContent());
        $this->assertEquals('css', $chain->getContentType());
    }
}
