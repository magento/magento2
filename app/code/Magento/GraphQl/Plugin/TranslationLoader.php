<?php

namespace Magento\GraphQl\Plugin;

use Magento\Framework\App\AreaInterface;
use Magento\Framework\App\AreaList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\State;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Load translations for GraphQL requests
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
     * Before rendering any string ensure the translation aspect of area is loaded
     */
    public function beforeRender(\Magento\Framework\Phrase $subject)
    {
        $area = $this->areaList->getArea($this->appState->getAreaCode());
        $area->load(AreaInterface::PART_TRANSLATE);
    }
}
