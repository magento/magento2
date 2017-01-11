<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Deploy\Model;

use Magento\Deploy\Console\Command\DeployStaticOptionsInterface;
use Magento\Framework\View\Asset\PreProcessor\AlternativeSourceInterface;
use Magento\Framework\App\ObjectManagerFactory;
use Magento\Framework\App\View\Deployment\Version;
use Magento\Framework\App\Utility\Files;
use Magento\Framework\Translate\Js\Config as JsTranslationConfig;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Deploy\Model\DeployManagerFactory;

/**
 * A service for deploying Magento static view files for production mode
 *
 * @deprecated
 * @see Use DeployManager::deploy instead
 */
class Deployer
{
    /** @var OutputInterface */
    private $output;

    /**
     * @var JsTranslationConfig
     */
    protected $jsTranslationConfig;

    /**
     * @var array
     */
    private $options;

    /**
     * @var DeployManagerFactory
     */
    private $deployManagerFactory;

    /**
     * Constructor
     *
     * @param Files $filesUtil
     * @param OutputInterface $output
     * @param Version\StorageInterface $versionStorage
     * @param JsTranslationConfig $jsTranslationConfig
     * @param AlternativeSourceInterface[] $alternativeSources
     * @param DeployManagerFactory $deployManagerFactory
     * @param array $options
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        Files $filesUtil,
        OutputInterface $output,
        Version\StorageInterface $versionStorage,
        JsTranslationConfig $jsTranslationConfig,
        array $alternativeSources,
        DeployManagerFactory $deployManagerFactory = null,
        $options = []
    ) {
        $this->output = $output;
        $this->deployManagerFactory = $deployManagerFactory;
        if (is_array($options)) {
            $this->options = $options;
        } else {
            // backward compatibility support
            $this->options = [DeployStaticOptionsInterface::DRY_RUN => (bool)$options];
        }
    }

    /**
     * @return \Magento\Deploy\Model\DeployManagerFactory
     */
    private function getDeployManagerFactory()
    {
        if (null === $this->deployManagerFactory) {
            $this->deployManagerFactory = ObjectManager::getInstance()->get(DeployManagerFactory::class);
        }

        return $this->deployManagerFactory;
    }

    /**
     * Populate all static view files for specified root path and list of languages
     *
     * @param ObjectManagerFactory $omFactory
     * @param array $locales
     * @param array $deployableAreaThemeMap
     * @return int
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @deprecated
     */
    public function deploy(ObjectManagerFactory $omFactory, array $locales, array $deployableAreaThemeMap = [])
    {
        /** @var DeployManager $deployerManager */
        $deployerManager = $this->getDeployManagerFactory()->create(
            ['options' => $this->options, 'output' => $this->output]
        );

        foreach ($deployableAreaThemeMap as $area => $themes) {
            foreach ($locales as $locale) {
                foreach ($themes as $themePath) {
                    $deployerManager->addPack($area, $themePath, $locale);
                }
            }
        }
        return $deployerManager->deploy();
    }

    /**
     * Set application locale and load translation for area
     *
     * @param string $locale
     * @param string $area
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @deprecated
     */
    protected function emulateApplicationLocale($locale, $area)
    {
    }
}
