<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Deploy\Model;

use Magento\Deploy\Model\Deploy\DeployInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\View\Deployment\Version\StorageInterface;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\View\Design\Fallback\Rule\RuleInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\Design\Fallback\RulePool;
use Magento\Framework\App\ObjectManagerFactory;
use Magento\Framework\View\Template\Html\MinifierInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\State;
use Magento\Framework\View\Asset\ConfigInterface as AssetConfig;
use Magento\Deploy\Console\Command\DeployStaticOptionsInterface as Options;
use Magento\Framework\App\Utility\Files;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DeployManager
{
    /**
     * Standard deploy strategy
     */
    const DEPLOY_STRATEGY_STANDARD = 'standard';

    /**
     * Quick deploy strategy
     */
    const DEPLOY_STRATEGY_QUICK = 'quick';

    /**
     * Base locale without customizations
     */
    const DEPLOY_BASE_LOCALE = 'deploy_base_locale';

    /**
     * @var array
     */
    private $packages = [];

    /**
     * @var DeployInterface[]
     */
    private $deployStrategies;

    /**
     * @var RulePool
     */
    private $rulePool;

    /**
     * @var RuleInterface
     */
    private $fallBackRule;

    /**
     * @var array
     */
    private $moduleDirectories;

    /**
     * @var ObjectManagerFactory
     */
    private $objectManagerFactory;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var DesignInterface
     */
    private $design;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var array
     */
    private $options;

    /**
     * @var AssetConfig
     */
    private $assetConfig;

    /**
     * @var Files
     */
    private $filesUtils;

    /**
     * @var StorageInterface
     */
    private $versionStorage;

    /**
     * @var MinifierInterface
     */
    private $htmlMinifier;

    /**
     * @param ObjectManagerFactory $objectManagerFactory
     * @param OutputInterface $output
     * @param RulePool $rulePool
     * @param DesignInterface $design
     * @param AssetConfig $assetConfig
     * @param Files $filesUtils
     * @param StorageInterface $versionStorage
     * @param MinifierInterface $htmlMinifier
     * @param array $options
     */
    public function __construct(
        ObjectManagerFactory $objectManagerFactory,
        OutputInterface $output,
        RulePool $rulePool,
        DesignInterface $design,
        AssetConfig $assetConfig,
        Files $filesUtils,
        StorageInterface $versionStorage,
        MinifierInterface $htmlMinifier,
        array $options
    ) {
        $this->rulePool = $rulePool;
        $this->objectManagerFactory = $objectManagerFactory;
        $this->design = $design;
        $this->output = $output;
        $this->options = $options;
        $this->assetConfig = $assetConfig;
        $this->filesUtils = $filesUtils;
        $this->versionStorage = $versionStorage;
        $this->htmlMinifier = $htmlMinifier;
    }

    /**
     * Add package tie to area and theme
     *
     * @param string $area
     * @param string $themePath
     * @param string $locale
     * @return void
     */
    public function addPack($area, $themePath, $locale)
    {
        $this->packages[$area . '-' . $themePath][$locale] = [$area, $themePath];
    }

    /**
     * Deploy local packages with chosen deploy strategy
     * @return int
     */
    public function deploy()
    {
        if (isset($this->options[Options::DRY_RUN]) && $this->options[Options::DRY_RUN]) {
            $this->output->writeln('Dry run. Nothing will be recorded to the target directory.');
        }

        $result = 0;
        foreach ($this->packages as $package) {
            $locales = array_keys($package);
            list($area, $themePath) = current($package);
            $this->emulateApplicationArea($area);

            if (count($locales) == 1) {
                $result |= $this->getDeployStrategy(self::DEPLOY_STRATEGY_STANDARD)
                    ->deploy($area, $themePath, current($locales), $this->options);
                continue;
            }

            $baseLocale = null;
            foreach ($this->getDeployStrategies($area, $themePath, $locales) as $locale => $strategy) {
                // base locale must processed first
                $baseLocale = $baseLocale ?: $locale;
                $this->options[self::DEPLOY_BASE_LOCALE] = $baseLocale;
                $result |= $strategy->deploy($area, $themePath, $locale, $this->options);
            }
        }

        return $result;
    }

    /**
     * Minify template files
     * @return void
     */
    public function minifyTemplates()
    {
        $noHtmlMinify = isset($this->options[Options::NO_HTML_MINIFY]) ? $this->options[Options::NO_HTML_MINIFY] : null;
        if (!($noHtmlMinify ?: !$this->assetConfig->isMinifyHtml())) {
            $this->output->writeln('=== Minify templates ===');
            $count = 0;
            foreach ($this->filesUtils->getPhtmlFiles(false, false) as $template) {
                $this->htmlMinifier->minify($template);
                if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                    $this->output->writeln($template . " minified\n");
                } else {
                    $this->output->write('.');
                }
                $count++;
            }
            $this->output->writeln("\nSuccessful: {$count} files modified\n---\n");
        }
    }

    /**
     * Save version of deployed files
     * @return void
     */
    public function saveDeployedVersion()
    {
        $version = (new \DateTime())->getTimestamp();
        $this->output->writeln("New version of deployed files: {$version}");
        if (isset($this->options[Options::DRY_RUN]) && !$this->options[Options::DRY_RUN]) {
            $this->versionStorage->save($version);
        }
    }

    /**
     * Emulate application area
     *
     * @param string $areaCode
     * @return void
     */
    private function emulateApplicationArea($areaCode)
    {
        $this->objectManager = $this->objectManagerFactory->create([State::PARAM_MODE => State::MODE_PRODUCTION]);
        $this->objectManager->get(State::class)->setAreaCode($areaCode);
    }

    /**
     * @param array $params
     * @return array
     */
    private function getLocaleDirectories($params)
    {
        $dirs = $this->getFallbackRule()->getPatternDirs($params);

        return array_filter($dirs, function ($dir) {
            return strpos($dir, 'i18n');
        });
    }

    /**
     * Get directories which can contains theme customization
     * @param string $area
     * @param string $themePath
     * @param string $locale
     * @return array
     */
    private function getCustomizationDirectories($area, $themePath, $locale)
    {
        $customizationDirectories = [];
        $this->design->setDesignTheme($themePath, $area);

        $params = ['area' => $area, 'theme' => $this->design->getDesignTheme(), 'locale' => $locale];
        foreach ($this->getLocaleDirectories($params) as $patternDir) {
            $customizationDirectories[] = $patternDir;
        }

        if ($this->moduleDirectories === null) {
            $this->moduleDirectories = [];
            $componentRegistrar = new ComponentRegistrar();
            $this->moduleDirectories = array_keys($componentRegistrar->getPaths(ComponentRegistrar::MODULE));
        }

        foreach ($this->moduleDirectories as $moduleDir) {
            $params['module_name'] = $moduleDir;
            $patternDirs = $this->getLocaleDirectories($params);
            foreach ($patternDirs as $patternDir) {
                $customizationDirectories[] = $patternDir;
            }
        }

        return $customizationDirectories;
    }

    /**
     * @param string $area
     * @param string $themePath
     * @param array $locales
     * @return DeployInterface[]
     */
    private function getDeployStrategies($area, $themePath, array $locales)
    {
        $baseLocale = null;
        $deployStrategies = [];

        foreach ($locales as $locale) {
            $hasCustomization = false;
            foreach ($this->getCustomizationDirectories($area, $themePath, $locale) as $directory) {
                if (glob($directory . DIRECTORY_SEPARATOR . '*', GLOB_NOSORT)) {
                    $hasCustomization = true;
                    break;
                }
            }
            if ($baseLocale === null && !$hasCustomization) {
                $baseLocale = $locale;
            } else {
                $deployStrategies[$locale] = $this->getDeployStrategy(
                    $hasCustomization ? self::DEPLOY_STRATEGY_STANDARD : self::DEPLOY_STRATEGY_QUICK
                );

            }
        }
        $deployStrategies = array_merge(
            [$baseLocale => $this->getDeployStrategy(self::DEPLOY_STRATEGY_STANDARD)],
            $deployStrategies
        );

        return $deployStrategies;
    }

    /**
     * @return \Magento\Framework\View\Design\Fallback\Rule\RuleInterface
     */
    private function getFallbackRule()
    {
        if (null === $this->fallBackRule) {
            $this->fallBackRule = $this->rulePool->getRule(RulePool::TYPE_STATIC_FILE);
        }

        return $this->fallBackRule;
    }

    /**
     * @param string $type
     * @return DeployInterface
     */
    private function getDeployStrategy($type)
    {
        if (!isset($this->deployStrategies[$type])) {
            $strategyMap = [
                self::DEPLOY_STRATEGY_STANDARD => Deploy\LocaleDeploy::class,
                self::DEPLOY_STRATEGY_QUICK => Deploy\LocaleQuickDeploy::class,
            ];

            if (!isset($strategyMap[$type])) {
                throw new \InvalidArgumentException('Wrong deploy strategy type: ' . $type);
            }
            $this->deployStrategies[$type] = $this->objectManager->create(
                $strategyMap[$type],
                ['output' => $this->output]
            );
        }

        return $this->deployStrategies[$type];
    }
}
