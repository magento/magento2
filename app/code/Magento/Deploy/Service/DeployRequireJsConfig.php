<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Service;

use Magento\Framework\Locale\ResolverInterfaceFactory;
use Magento\Framework\Locale\ResolverInterface;
use Magento\RequireJs\Model\FileManagerFactory;
use Magento\Framework\View\DesignInterfaceFactory;
use Magento\Framework\View\Design\Theme\ListInterface;
use Magento\Framework\View\Asset\RepositoryFactory;
use Magento\Framework\RequireJs\ConfigFactory;

/**
 * Deploy RequireJS configuration service
 * @since 2.2.0
 */
class DeployRequireJsConfig
{
    /**
     * Default jobs amount
     */
    const DEFAULT_JOBS_AMOUNT = 4;

    /**
     * @var ListInterface
     * @since 2.2.0
     */
    private $themeList;

    /**
     * @var DesignInterfaceFactory
     * @since 2.2.0
     */
    private $designFactory;

    /**
     * @var RepositoryFactory
     * @since 2.2.0
     */
    private $assetRepoFactory;

    /**
     * @var FileManagerFactory
     * @since 2.2.0
     */
    private $fileManagerFactory;

    /**
     * @var ConfigFactory
     * @since 2.2.0
     */
    private $requireJsConfigFactory;

    /**
     * @var ResolverInterfaceFactory
     * @since 2.2.0
     */
    private $localeFactory;

    /**
     * DeployRequireJsConfig constructor
     *
     * @param ListInterface $themeList
     * @param DesignInterfaceFactory $designFactory
     * @param RepositoryFactory $assetRepoFactory
     * @param FileManagerFactory $fileManagerFactory
     * @param ConfigFactory $requireJsConfigFactory
     * @param ResolverInterfaceFactory $localeFactory
     * @since 2.2.0
     */
    public function __construct(
        ListInterface $themeList,
        DesignInterfaceFactory $designFactory,
        RepositoryFactory $assetRepoFactory,
        FileManagerFactory $fileManagerFactory,
        ConfigFactory $requireJsConfigFactory,
        ResolverInterfaceFactory $localeFactory
    ) {
        $this->themeList = $themeList;
        $this->designFactory = $designFactory;
        $this->assetRepoFactory = $assetRepoFactory;
        $this->fileManagerFactory = $fileManagerFactory;
        $this->requireJsConfigFactory = $requireJsConfigFactory;
        $this->localeFactory = $localeFactory;
    }

    /**
     * @param string $areaCode
     * @param string $themePath
     * @param string $localeCode
     * @return bool true on success
     * @since 2.2.0
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

        $fileManager->createRequireJsConfigAsset();

        $fileManager->createMinResolverAsset();

        return true;
    }
}
