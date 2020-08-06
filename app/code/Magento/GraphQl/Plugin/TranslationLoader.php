<?php

namespace Magento\GraphQl\Plugin;

use Magento\Framework\App\AreaInterface;
use Magento\Framework\App\AreaList;
use Magento\Framework\App\State;

/**
 * Load translations on the first instance of a translated string
 */
class TranslationLoader
{
    /**
     * @var AreaList
     */
    private $areaList;

    /**
     * @var State
     */
    private $appState;

    /**
     * @var bool
     */
    private $translationsLoaded = false;

    /**
     * @param AreaList $areaList
     * @param State $appState
     */
    public function __construct(
        AreaList $areaList,
        State $appState
    ) {
        $this->areaList = $areaList;
        $this->appState = $appState;
    }

    /**
     * Before render of any localized string ensure the translation data is loaded
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeRender()
    {
        if ($this->translationsLoaded === false) {
            $area = $this->areaList->getArea($this->appState->getAreaCode());
            $area->load(AreaInterface::PART_TRANSLATE);
            $this->translationsLoaded = true;
        }
    }
}
