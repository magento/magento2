<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Deploy\Service;

use Magento\Framework\Locale\ResolverInterfaceFactory;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\View\Asset\ConfigInterface;
use Magento\RequireJs\Model\FileManagerFactory;
use Magento\Framework\View\DesignInterfaceFactory;
use Magento\Framework\View\Design\Theme\ListInterface;
use Magento\Framework\View\Asset\RepositoryFactory;
use Magento\Framework\RequireJs\ConfigFactory;

/**
 * Deploy RequireJS configuration service
 */
class DeployRequireJsConfig
{
    public const DEFAULT_JOBS_AMOUNT = 4;

    /**
     * @var ListInterface
     */
    private $themeList;

    /**
     * @var DesignInterfaceFactory
     */
    private $designFactory;

    /**
     * @var RepositoryFactory
     */
    private $assetRepoFactory;

    /**
     * @var FileManagerFactory
     */
    private $fileManagerFactory;

    /**
     * @var ConfigFactory
     */
    private $requireJsConfigFactory;

    /**
     * @var ResolverInterfaceFactory
     */
    private $localeFactory;

    /**
     * @var ConfigInterface
     */
    private $bundleConfig;

    /**
     * DeployRequireJsConfig constructor
     *
     * @param ListInterface $themeList
     * @param DesignInterfaceFactory $designFactory
     * @param RepositoryFactory $assetRepoFactory
     * @param FileManagerFactory $fileManagerFactory
     * @param ConfigFactory $requireJsConfigFactory
     * @param ResolverInterfaceFactory $localeFactory
     * @param ConfigInterface $bundleConfig
     */
    public function __construct(
        ListInterface $themeList,
        DesignInterfaceFactory $designFactory,
        RepositoryFactory $assetRepoFactory,
        FileManagerFactory $fileManagerFactory,
        ConfigFactory $requireJsConfigFactory,
        ResolverInterfaceFactory $localeFactory,
        ConfigInterface $bundleConfig
    ) {
        $this->themeList = $themeList;
        $this->designFactory = $designFactory;
        $this->assetRepoFactory = $assetRepoFactory;
        $this->fileManagerFactory = $fileManagerFactory;
        $this->requireJsConfigFactory = $requireJsConfigFactory;
        $this->localeFactory = $localeFactory;
        $this->bundleConfig = $bundleConfig;
    }

    /**
     * Deploy RequireJS configuration for a specific theme and locale
     *
     * @param string $areaCode
     * @param string $themePath
     * @param string $localeCode
     * @return bool true on success
     */
    public function deploy($areaCode, $themePath, $localeCode)
    {
        /** @var \Magento\Framework\View\Design\ThemeInterface $theme */
        $theme = $this->themeList->getThemeByFullPath($areaCode . '/' . $themePath);
        /** @var \Magento\Theme\Model\View\Design $design */
        $design = $this->designFactory->create()->setDesignTheme($theme, $areaCode);
        /** @var ResolverInterface $locale */
        $locale = $this->localeFactory->create();
        $locale->setLocale($localeCode);
        $design->setLocale($locale);

        $assetRepo = $this->assetRepoFactory->create(['design' => $design]);
        /** @var \Magento\RequireJs\Model\FileManager $fileManager */
        $fileManager = $this->fileManagerFactory->create(
            [
                'config' => $this->requireJsConfigFactory->create(
                    [
                        'assetRepo' => $assetRepo,
                        'design' => $design,
                    ]
                ),
                'assetRepo' => $assetRepo,
            ]
        );

        if ($this->bundleConfig->isBundlingJsFiles()) {
            $fileManager->createStaticJsAsset();
            $fileManager->createRequireJsMixinsAsset();
        }
        $fileManager->createRequireJsConfigAsset();
        $fileManager->createMinResolverAsset();

        return true;
    }
}
