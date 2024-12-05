<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Css\Test\Unit\PreProcessor\Instruction;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Css\PreProcessor\ErrorHandlerInterface;
use Magento\Framework\Css\PreProcessor\Instruction\Import;
use Magento\Framework\Css\PreProcessor\Instruction\MagentoImport;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Asset\File;
use Magento\Framework\View\Asset\File\FallbackContext;
use Magento\Framework\View\Asset\PreProcessor\Chain;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Design\Theme\ListInterface as ThemeListInterface;
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
    private $designMock;

    /**
     * @var CollectorInterface|MockObject
     */
    private $fileSourceMock;

    /**
     * @var ErrorHandlerInterface|MockObject
     */
    private $errorHandlerMock;

    /**
     * @var File|MockObject
     */
    private $assetMock;

    /**
     * @var Repository|MockObject
     */
    private $assetRepoMock;

    /**
     * @var ThemeListInterface|MockObject
     */
    private $themeListMock;

    /**
     * @var DeploymentConfig|MockObject
     */
    private $deploymentConfigMock;

    /**
     * @var ModuleManager|MockObject
     */
    private $moduleManagerMock;

    /**
     * @var ThemeProviderInterface|MockObject
     */
    private $themeProviderMock;
    /**
     * @var Import
     */
    private $object;

    protected function setUp(): void
    {
        $this->designMock = $this->createMock(DesignInterface::class);
        $this->fileSourceMock = $this->createMock(CollectorInterface::class);
        $this->errorHandlerMock = $this->createMock(ErrorHandlerInterface::class);
        $this->assetMock = $this->createMock(File::class);
        $this->assetMock->method('getContentType')->willReturn('css');
        $this->assetRepoMock = $this->createMock(Repository::class);
        $this->themeListMock = $this->createMock(ThemeListInterface::class);
        $this->deploymentConfigMock = $this->createMock(DeploymentConfig::class);
        $this->moduleManagerMock = $this->createMock(ModuleManager::class);

        $this->themeProviderMock = $this->createMock(ThemeProviderInterface::class);

        $this->object = (new ObjectManager($this))->getObject(MagentoImport::class, [
            'design' => $this->designMock,
            'fileSource' => $this->fileSourceMock,
            'errorHandler' => $this->errorHandlerMock,
            'assetRepo' => $this->assetRepoMock,
            'themeList' => $this->themeListMock,
            'deploymentConfig' => $this->deploymentConfigMock,
            'moduleManager' => $this->moduleManagerMock,
            // Mocking private property
            'themeProvider' => $this->themeProviderMock,
        ]);
    }

    /**
     * @param string $originalContent
     * @param string $foundPath
     * @param string $resolvedPath
     * @param array $foundFiles
     * @param string $expectedContent
     * @param array $enabledModules
     * @param bool $onlyEnabled
     * @dataProvider processDataProvider
     */
    public function testProcess(
        string $originalContent,
        string $foundPath,
        string $resolvedPath,
        array $foundFiles,
        string $expectedContent,
        array $enabledModules,
        bool $onlyEnabled
    ): void {
        $chain = new Chain($this->assetMock, $originalContent, 'css', 'path');
        $relatedAsset = $this->createMock(File::class);
        $relatedAsset->expects($this->once())
            ->method('getFilePath')
            ->willReturn($resolvedPath);
        $context = $this->createMock(FallbackContext::class);
        $this->assetRepoMock->expects($this->once())
            ->method('createRelated')
            ->with($foundPath, $this->assetMock)
            ->willReturn($relatedAsset);
        $relatedAsset->expects($this->once())->method('getContext')->willReturn($context);
        $theme = $this->getMockForAbstractClass(ThemeInterface::class);
        $this->themeProviderMock->expects($this->once())->method('getThemeByFullPath')->willReturn($theme);
        $files = [];
        foreach ($foundFiles as $file) {
            $fileObject = $this->createMock(\Magento\Framework\View\File::class);
            $fileObject
                ->method('getModule')
                ->willReturn($file['module']);
            $fileObject
                ->method('getFilename')
                ->willReturn($file['filename']);
            $files[] = $fileObject;
        }
        $this->fileSourceMock->expects($this->once())
            ->method('getFiles')
            ->with($theme, $resolvedPath)
            ->willReturn($files);

        $this->deploymentConfigMock->method('get')->with('static_content_only_enabled_modules')
            ->willReturn($onlyEnabled);

        $this->moduleManagerMock->method('isEnabled')
            ->willReturnCallback(function ($moduleName) use ($enabledModules) {
                return in_array($moduleName, $enabledModules, true);
            });

        $this->object->process($chain);
        $this->assertEquals($expectedContent, $chain->getContent());
        $this->assertEquals('css', $chain->getContentType());
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return array
     */
    public static function processDataProvider()
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
                [],
                true
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
                ['Magento_Module', 'Magento_Two'],
                true
            ],
            'modular with disabled module' => [
                '//@magento_import "Magento_Module::some/file.css";',
                'Magento_Module::some/file.css',
                'some/file.css',
                [
                    ['module' => 'Magento_Module', 'filename' => 'some/file.css'],
                    ['module' => 'Magento_Two', 'filename' => 'some/file.css'],
                ],
                "@import 'Magento_Two::some/file.css';\n",
                ['Magento_Two'],
                true
            ],
            'modular with disabled all modules' => [
                '//@magento_import "Magento_Module::some/file.css";',
                'Magento_Module::some/file.css',
                'some/file.css',
                [
                    ['module' => 'Magento_Module', 'filename' => 'some/file.css'],
                    ['module' => 'Magento_Two', 'filename' => 'some/file.css'],
                ],
                '',
                [],
                true
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
                [],
                true
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
                ['Magento_Module', 'Magento_Two'],
                true
            ],
            'modular reference with disabled module' => [
                '//@magento_import (reference) "Magento_Module::some/file.css";',
                'Magento_Module::some/file.css',
                'some/file.css',
                [
                    ['module' => 'Magento_Module', 'filename' => 'some/file.css'],
                    ['module' => 'Magento_Two', 'filename' => 'some/file.css'],
                ],
                "@import (reference) 'Magento_Module::some/file.css';\n",
                ['Magento_Module'],
                true
            ],
            'modular reference with disabled module and disabled "only enabled modules" flag' => [
                '//@magento_import (reference) "Magento_Module::some/file.css";',
                'Magento_Module::some/file.css',
                'some/file.css',
                [
                    ['module' => 'Magento_Module', 'filename' => 'some/file.css'],
                    ['module' => 'Magento_Two', 'filename' => 'some/file.css'],
                ],
                "@import (reference) 'Magento_Module::some/file.css';\n" .
                "@import (reference) 'Magento_Two::some/file.css';\n",
                ['Magento_Module'],
                false
            ],
            'modular reference with disabled all modules' => [
                '//@magento_import (reference) "Magento_Module::some/file.css";',
                'Magento_Module::some/file.css',
                'some/file.css',
                [
                    ['module' => 'Magento_Module', 'filename' => 'some/file.css'],
                    ['module' => 'Magento_Two', 'filename' => 'some/file.css'],
                ],
                '',
                [],
                true
            ],
            'modular reference with disabled all modules and disabled "only enabled modules" flag' => [
                '//@magento_import (reference) "Magento_Module::some/file.css";',
                'Magento_Module::some/file.css',
                'some/file.css',
                [
                    ['module' => 'Magento_Module', 'filename' => 'some/file.css'],
                    ['module' => 'Magento_Two', 'filename' => 'some/file.css'],
                ],
                "@import (reference) 'Magento_Module::some/file.css';\n" .
                "@import (reference) 'Magento_Two::some/file.css';\n",
                [],
                false
            ],
        ];
    }

    public function testProcessNoImport(): void
    {
        $originalContent = 'color: #000000;';
        $expectedContent = 'color: #000000;';
        $chain = new Chain($this->assetMock, $originalContent, 'css', 'orig');
        $this->assetRepoMock->expects($this->never())
            ->method('createRelated');
        $this->object->process($chain);
        $this->assertEquals($expectedContent, $chain->getContent());
        $this->assertEquals('css', $chain->getContentType());
    }

    public function testProcessException(): void
    {
        $chain = new Chain(
            $this->assetMock,
            '//@magento_import "some/file.css";',
            'css',
            'path'
        );
        $exception = new \LogicException('Error happened');
        $this->assetRepoMock->expects($this->once())
            ->method('createRelated')
            ->willThrowException($exception);
        $this->errorHandlerMock->expects($this->once())
            ->method('processException')
            ->with($exception);
        $this->object->process($chain);
        $this->assertEquals('', $chain->getContent());
        $this->assertEquals('css', $chain->getContentType());
    }
}
