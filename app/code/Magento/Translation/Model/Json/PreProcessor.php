<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Translation\Model\Json;

use Magento\Framework\App\AreaList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\TranslateInterface;
use Magento\Framework\View\Asset\File\FallbackContext;
use Magento\Framework\View\Asset\PreProcessor\Chain;
use Magento\Framework\View\Asset\PreProcessorInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Translation\Model\Js\Config;
use Magento\Translation\Model\Js\DataProviderInterface;

/**
 * PreProcessor responsible for providing js translation dictionary
 */
class PreProcessor implements PreProcessorInterface
{
    /**
     * Js translation configuration
     *
     * @var Config
     */
    protected $config;

    /**
     * Translation data provider
     *
     * @var DataProviderInterface
     */
    protected $dataProvider;

    /**
     * @var AreaList
     */
    protected $areaList;

    /**
     * @var TranslateInterface
     */
    protected $translate;

    /**
     * @var DesignInterface
     */
    private $viewDesign;

    /**
     * @param Config $config
     * @param DataProviderInterface $dataProvider
     * @param AreaList $areaList
     * @param TranslateInterface $translate
     * @param DesignInterface|null $viewDesign
     */
    public function __construct(
        Config $config,
        DataProviderInterface $dataProvider,
        AreaList $areaList,
        TranslateInterface $translate,
        DesignInterface $viewDesign = null
    ) {
        $this->config = $config;
        $this->dataProvider = $dataProvider;
        $this->areaList = $areaList;
        $this->translate = $translate;
        $this->viewDesign = $viewDesign ?: ObjectManager::getInstance()->get(DesignInterface::class);
    }

    /**
     * Transform content and/or content type for the specified preprocessing chain object
     *
     * @param Chain $chain
     * @return void
     * @throws \Exception
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

                $this->viewDesign->setDesignTheme($themePath, $areaCode);

                $this->translate
                    ->setLocale($context->getLocale())
                    ->loadData($areaCode);
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
     */
    protected function isDictionaryPath($path)
    {
        return (strpos($path, $this->config->getDictionaryFileName()) !== false);
    }
}
