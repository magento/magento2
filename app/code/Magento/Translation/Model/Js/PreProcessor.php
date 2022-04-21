<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Translation\Model\Js;

use Magento\Framework\App\AreaList;
use Magento\Framework\TranslateInterface;
use Magento\Framework\View\Asset\File\FallbackContext;
use Magento\Framework\View\Asset\PreProcessor\Chain;
use Magento\Framework\View\Asset\PreProcessorInterface;

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
     * @param Config $config
     * @param AreaList $areaList
     * @param TranslateInterface $translate
     */
    public function __construct(Config $config, AreaList $areaList, TranslateInterface $translate)
    {
        $this->config = $config;
        $this->areaList = $areaList;
        $this->translate = $translate;
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

            $chain->setContent($this->translate($chain->getContent()));
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
                [$context->getLocale()]
        )) {
            $this->areasThemesLocales[$context->getAreaCode()]
                [$context->getThemePath()]
                [$context->getLocale()] = true;
            $this->translate->loadData($context->getAreaCode(), false);
        }
    }
}
