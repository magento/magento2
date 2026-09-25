<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\View;

class DesignLoader
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\Framework\App\AreaList
     */
    protected $_areaList;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $appState;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\App\AreaList $areaList
     * @param \Magento\Framework\App\State $appState
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\AreaList $areaList,
        \Magento\Framework\App\State $appState
    ) {
        $this->_request = $request;
        $this->_areaList = $areaList;
        $this->appState = $appState;
    }

    /**
     * Load design
     *
     * @return void
     */
    public function load()
    {
        $area = $this->_areaList->getArea($this->appState->getAreaCode());
        $area->load(\Magento\Framework\App\Area::PART_DESIGN);
        $area->load(\Magento\Framework\App\Area::PART_TRANSLATE);
        $area->detectDesign($this->_request);
    }

    /**
     * Load design part of the current area
     *
     * @return void
     */
    public function loadDesign(): void
    {
        $this->getArea()->load(\Magento\Framework\App\Area::PART_DESIGN);
    }

    /**
     * Load translations of the current area
     *
     * Must be called after the design part is loaded, because theme translation
     * files are resolved from the design theme.
     *
     * @return void
     */
    public function loadTranslation(): void
    {
        $this->getArea()->load(\Magento\Framework\App\Area::PART_TRANSLATE);
    }

    /**
     * Apply store design change or user-agent design exception
     *
     * Must be called after the translations are loaded to keep the design change
     * from affecting the set of loaded translation files.
     *
     * @return void
     */
    public function applyDesignChange(): void
    {
        $this->getArea()->detectDesign($this->_request);
    }

    /**
     * Get the area of the current application scope
     *
     * @return \Magento\Framework\App\AreaInterface
     */
    private function getArea(): \Magento\Framework\App\AreaInterface
    {
        return $this->_areaList->getArea($this->appState->getAreaCode());
    }
}
