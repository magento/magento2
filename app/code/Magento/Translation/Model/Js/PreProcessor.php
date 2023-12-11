<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Translation\Model\Js;

use Magento\Deploy\Console\DeployStaticOptions;
use Magento\Framework\App\AreaList;
use Magento\Framework\TranslateInterface;
use Magento\Framework\View\Asset\File\FallbackContext;
use Magento\Framework\View\Asset\PreProcessor\Chain;
use Magento\Framework\View\Asset\PreProcessorInterface;
use Symfony\Component\Console\Input\ArgvInput;
use Magento\Deploy\Strategy\DeployStrategyFactory;

/**
 * PreProcessor responsible for replacing translation calls in js files to translated strings
 */
class PreProcessor implements PreProcessorInterface
{
    /**
     * Javascript translation configuration
     *
     * @var Config
     */
    protected $config;

    /**
     * @var AreaList
     */
    protected $areaList;

    /**
     * @var TranslateInterface
     */
    protected $translate;

    /**
     * @var array
     */
    protected $areasThemesLocales = [];

    /**
     * @var ArgvInput
     */
    private $input;

    /**
     * @param Config $config
     * @param AreaList $areaList
     * @param TranslateInterface $translate
     * @param ArgvInput $input
     */
    public function __construct(
        Config $config,
        AreaList $areaList,
        TranslateInterface $translate,
        ArgvInput $input
    ) {
        $this->config = $config;
        $this->areaList = $areaList;
        $this->translate = $translate;
        $this->input = $input;
    }

    /**
     * Transform content and/or content type for the specified preprocessing chain object
     *
     * @param Chain $chain
     * @return void
     */
    public function process(Chain $chain)
    {
        if ($this->config->isEmbeddedStrategy()) {
            $context = $chain->getAsset()->getContext();

            $areaCode = \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE;

            if ($context instanceof FallbackContext) {
                $areaCode = $context->getAreaCode();
                $this->translate->setLocale($context->getLocale());
                $this->loadTranslationDataBasedOnThemesAndLocales($context);
            }

            $area = $this->areaList->getArea($areaCode);
            $area->load(\Magento\Framework\App\Area::PART_TRANSLATE);

            if (!$this->isCheckStrategyCompact()) {
                $chain->setContent($this->translate($chain->getContent()));
            } else {
                $chain->setContent($chain->getContent());
            }
        }
    }

    /**
     * Replace translation calls with translation result and return content
     *
     * @param string $content
     * @return string
     */
    public function translate($content)
    {
        foreach ($this->config->getPatterns() as $pattern) {
            $content = preg_replace_callback($pattern, [$this, 'replaceCallback'], $content);
        }
        return $content;
    }

    /**
     * Replace callback for preg_replace_callback function
     *
     * @param array $matches
     * @return string
     */
    protected function replaceCallback($matches)
    {
        return '\'' . __($matches['translate']) . '\'';
    }

    /**
     * Load translation data based on themes and locales.
     *
     * @param FallbackContext $context
     * @return void
     */
    public function loadTranslationDataBasedOnThemesAndLocales(FallbackContext $context): void
    {
        if (!isset($this->areasThemesLocales[$context->getAreaCode()]
                [$context->getThemePath()]
                [$context->getLocale()])) {
            $this->areasThemesLocales[$context->getAreaCode()]
                [$context->getThemePath()]
                [$context->getLocale()] = true;
            $this->translate->loadData($context->getAreaCode(), false);
        }
    }

    /**
     * Check deploy argument strategy is compact.
     *
     * @return bool
     */
    public function isCheckStrategyCompact(): bool
    {
        $isCompact = false;
        $isStrategy = $this->input->hasParameterOption('--' . DeployStaticOptions::STRATEGY) ||
            $this->input->hasParameterOption('-s');
        if ($isStrategy) {
            $strategyValue = $this->input->getParameterOption('--' . DeployStaticOptions::STRATEGY) ?:
                $this->input->getParameterOption('-s');
            if ($strategyValue === DeployStrategyFactory::DEPLOY_STRATEGY_COMPACT) {
                $isCompact = true;
            }
        }
        return  $isCompact;
    }
}
