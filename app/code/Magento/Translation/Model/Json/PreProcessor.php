<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Translation\Model\Json;

use Magento\Framework\View\Asset\PreProcessorInterface;
use Magento\Translation\Model\Js\Config;
use Magento\Translation\Model\Js\DataProviderInterface;
use Magento\Framework\View\Asset\PreProcessor\Chain;
use Magento\Framework\View\Asset\File\FallbackContext;
use Magento\Framework\App\AreaList;
use Magento\Framework\TranslateInterface;

/**
 * PreProcessor responsible for providing js translation dictionary
 * @since 2.0.0
 */
class PreProcessor implements PreProcessorInterface
{
    /**
     * Js translation configuration
     *
     * @var Config
     * @since 2.0.0
     */
    protected $config;

    /**
     * Translation data provider
     *
     * @var DataProviderInterface
     * @since 2.0.0
     */
    protected $dataProvider;

    /**
     * @var AreaList
     * @since 2.0.0
     */
    protected $areaList;

    /**
     * @var TranslateInterface
     * @since 2.0.0
     */
    protected $translate;

    /**
     * @param Config $config
     * @param DataProviderInterface $dataProvider
     * @param AreaList $areaList
     * @param TranslateInterface $translate
     * @since 2.0.0
     */
    public function __construct(
        Config $config,
        DataProviderInterface $dataProvider,
        AreaList $areaList,
        TranslateInterface $translate
    ) {
        $this->config = $config;
        $this->dataProvider = $dataProvider;
        $this->areaList = $areaList;
        $this->translate = $translate;
    }

    /**
     * Transform content and/or content type for the specified preprocessing chain object
     *
     * @param Chain $chain
     * @return void
     * @since 2.0.0
     */
    public function process(Chain $chain)
    {
        if ($this->isDictionaryPath($chain->getTargetAssetPath())) {
            $context = $chain->getAsset()->getContext();

            $themePath = '*/*';
            $areaCode = \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE;

            if ($context instanceof FallbackContext) {
                $themePath = $context->getThemePath();
                $areaCode = $context->getAreaCode();
                $this->translate->setLocale($context->getLocale());
            }

            $area = $this->areaList->getArea($areaCode);
            $area->load(\Magento\Framework\App\Area::PART_TRANSLATE);

            $chain->setContent(json_encode($this->dataProvider->getData($themePath)));
            $chain->setContentType('json');
        }
    }

    /**
     * Is provided path the path to translation dictionary
     *
     * @param string $path
     * @return bool
     * @since 2.0.0
     */
    protected function isDictionaryPath($path)
    {
        return (strpos($path, $this->config->getDictionaryFileName()) !== false);
    }
}
