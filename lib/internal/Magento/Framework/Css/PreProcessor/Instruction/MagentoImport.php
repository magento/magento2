<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Css\PreProcessor\Instruction;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Css\PreProcessor\ErrorHandlerInterface;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\Framework\View\Asset\File\FallbackContext;
use Magento\Framework\View\Asset\LocalInterface;
use Magento\Framework\View\Asset\PreProcessorInterface;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Framework\View\Design\Theme\ListInterface as ThemeListInterface;
use Magento\Framework\View\Design\Theme\ThemeProviderInterface;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\File\CollectorInterface;
use Magento\Framework\View\Asset\PreProcessor\Chain;

/**
 * @magento_import instruction preprocessor
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) Must be deleted after moving themeProvider to construct
 */
class MagentoImport implements PreProcessorInterface, ResetAfterRequestInterface
{
    /**
     * PCRE pattern that matches @magento_import instruction
     */
    public const REPLACE_PATTERN =
        '#//@magento_import(?P<reference>\s+\(reference\))?\s+[\'\"](?P<path>(?![/\\\]|\w:[/\\\])[^\"\']+)[\'\"]\s*?;#';

    private const CONFIG_PATH_SCD_ONLY_ENABLED_MODULES = 'static_content_only_enabled_modules';

    /**
     * @var DesignInterface
     */
    protected $design;

    /**
     * @var CollectorInterface
     */
    protected $fileSource;

    /**
     * @var ErrorHandlerInterface
     */
    protected $errorHandler;

    /**
     * @var AssetRepository
     */
    protected $assetRepo;

    /**
     * @var ThemeListInterface
     * @deprecated 100.0.2
     * @see not used
     */
    protected $themeList;

    /**
     * @var ThemeProviderInterface|null
     */
    private $themeProvider;

    /**
     * @var DeploymentConfig
     */
    private DeploymentConfig $deploymentConfig;

    /**
     * @var ModuleManager
     */
    private ModuleManager $moduleManager;

    /**
     * @param DesignInterface $design
     * @param CollectorInterface $fileSource
     * @param ErrorHandlerInterface $errorHandler
     * @param AssetRepository $assetRepo
     * @param ThemeListInterface $themeList
     * @param DeploymentConfig|null $deploymentConfig
     * @param ModuleManager|null $moduleManager
     */
    public function __construct(
        DesignInterface $design,
        CollectorInterface $fileSource,
        ErrorHandlerInterface $errorHandler,
        AssetRepository $assetRepo,
        ThemeListInterface $themeList,
        ?DeploymentConfig $deploymentConfig = null,
        ?ModuleManager $moduleManager = null
    ) {
        $this->design = $design;
        $this->fileSource = $fileSource;
        $this->errorHandler = $errorHandler;
        $this->assetRepo = $assetRepo;
        $this->themeList = $themeList;
        $this->deploymentConfig = $deploymentConfig ?? ObjectManager::getInstance() ->get(DeploymentConfig::class);
        $this->moduleManager = $moduleManager ?? ObjectManager::getInstance()->get(ModuleManager::class);
    }

    /**
     * @inheritDoc
     */
    public function process(Chain $chain)
    {
        $asset = $chain->getAsset();
        $replaceCallback = function ($matchContent) use ($asset) {
            return $this->replace($matchContent, $asset);
        };
        $chain->setContent(preg_replace_callback(self::REPLACE_PATTERN, $replaceCallback, $chain->getContent()));
    }

    /**
     * Replace @magento_import to @import instructions
     *
     * @param array $matchedContent
     * @param LocalInterface $asset
     * @return string
     */
    protected function replace(array $matchedContent, LocalInterface $asset)
    {
        $importsContent = '';
        try {
            $matchedFileId = $matchedContent['path'];
            $isReference = !empty($matchedContent['reference']);
            $relatedAsset = $this->assetRepo->createRelated($matchedFileId, $asset);
            $resolvedPath = $relatedAsset->getFilePath();
            $importFiles = $this->fileSource->getFiles($this->getTheme($relatedAsset), $resolvedPath);
            $deployOnlyEnabled = $this->hasEnabledFlagDeployEnabledModules();
            /** @var $importFile \Magento\Framework\View\File */
            foreach ($importFiles as $importFile) {
                $moduleName = $importFile->getModule();
                $referenceString = $isReference ? '(reference) ' : '';

                if ($moduleName) {
                    if (!$deployOnlyEnabled || $this->moduleManager->isEnabled($moduleName)) {
                        $importsContent .= "@import $referenceString'{$moduleName}::{$resolvedPath}';\n";
                    }
                } else {
                    $importsContent .= "@import $referenceString'{$matchedFileId}';\n";
                }
            }
        } catch (\LogicException $e) {
            $this->errorHandler->processException($e);
        }

        return $importsContent;
    }

    /**
     * Retrieve flag deploy enabled modules
     *
     * @return bool
     */
    private function hasEnabledFlagDeployEnabledModules(): bool
    {
        return (bool) $this->deploymentConfig->get(self::CONFIG_PATH_SCD_ONLY_ENABLED_MODULES);
    }

    /**
     * Get theme model based on the information from asset
     *
     * @param LocalInterface $asset
     * @return ThemeInterface
     */
    protected function getTheme(LocalInterface $asset)
    {
        $context = $asset->getContext();
        if ($context instanceof FallbackContext) {
            return $this->getThemeProvider()->getThemeByFullPath(
                $context->getAreaCode() . '/' . $context->getThemePath()
            );
        }

        return $this->design->getDesignTheme();
    }

    /**
     * Gets themeProvider, lazy loading it when needed
     *
     * @return ThemeProviderInterface
     */
    private function getThemeProvider(): ThemeProviderInterface
    {
        if (null === $this->themeProvider) {
            $this->themeProvider = ObjectManager::getInstance()->get(ThemeProviderInterface::class);
        }
        return $this->themeProvider;
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->themeProvider = null;
    }
}
