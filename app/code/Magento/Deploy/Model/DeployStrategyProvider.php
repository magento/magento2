<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Deploy\Model;

use Magento\Deploy\Model\Deploy\DeployInterface;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Module\Dir;
use Magento\Framework\View\Design\Fallback\Rule\RuleInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\Design\Fallback\RulePool;
use Symfony\Component\Console\Output\OutputInterface;

class DeployStrategyProvider
{
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
     * @var DeployStrategyFactory
     */
    private $deployStrategyFactory;

    /**
     * @param OutputInterface $output
     * @param RulePool $rulePool
     * @param DesignInterface $design
     * @param DeployStrategyFactory $deployStrategyFactory
     * @param array $options
     */
    public function __construct(
        OutputInterface $output,
        RulePool $rulePool,
        DesignInterface $design,
        DeployStrategyFactory $deployStrategyFactory,
        array $options
    ) {
        $this->rulePool = $rulePool;
        $this->design = $design;
        $this->output = $output;
        $this->options = $options;
        $this->deployStrategyFactory = $deployStrategyFactory;
    }

    /**
     * @param string $area
     * @param string $themePath
     * @param array $locales
     * @return DeployInterface[]
     */
    public function getDeployStrategies($area, $themePath, array $locales)
    {
        if (count($locales) == 1) {
            $locale = current($locales);
            return [$locale => $this->getDeployStrategy(DeployStrategyFactory::DEPLOY_STRATEGY_STANDARD)];
        }

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
                $deployStrategies[$locale] = $hasCustomization
                    ? DeployStrategyFactory::DEPLOY_STRATEGY_STANDARD
                    : DeployStrategyFactory::DEPLOY_STRATEGY_QUICK;
            }
        }
        $deployStrategies = array_merge(
            [$baseLocale => DeployStrategyFactory::DEPLOY_STRATEGY_STANDARD],
            $deployStrategies
        );

        return array_map(function ($strategyType) use ($area, $baseLocale) {
            return $this->getDeployStrategy($strategyType, $baseLocale);
        }, $deployStrategies);
    }

    /**
     * @param array $params
     * @return array
     */
    private function getLocaleDirectories($params)
    {
        $dirs = $this->getFallbackRule()->getPatternDirs($params);

        return array_filter($dirs, function ($dir) {
            return strpos($dir, Dir::MODULE_I18N_DIR);
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
     * @param null|string $baseLocale
     * @return DeployInterface
     */
    private function getDeployStrategy($type, $baseLocale = null)
    {
        $options = $this->options;
        if ($baseLocale) {
            $options[DeployInterface::DEPLOY_BASE_LOCALE] = $baseLocale;
        }

        return $this->deployStrategyFactory->create(
            $type,
            ['output' => $this->output, 'options' => $options]
        );
    }
}
