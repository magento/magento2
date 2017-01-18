<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Translation\Model\Js;

use Magento\Framework\View\Asset\PreProcessorInterface;
use Magento\Framework\View\Asset\PreProcessor\Chain;
use Magento\Framework\Filesystem;
use Magento\Framework\View\Asset\File\FallbackContext;
use Magento\Framework\App\AreaList;
use Magento\Framework\TranslateInterface;

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
        return '"' . __($matches[1]) . '"';
    }
}
