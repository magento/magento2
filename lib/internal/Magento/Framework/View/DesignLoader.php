<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View;

/**
 * Class \Magento\Framework\View\DesignLoader
 *
 * @since 2.0.0
 */
class DesignLoader
{
    /**
     * Request
     *
     * @var \Magento\Framework\App\RequestInterface
     * @since 2.0.0
     */
    protected $_request;

    /**
     * Application
     *
     * @var \Magento\Framework\App\AreaList
     * @since 2.0.0
     */
    protected $_areaList;

    /**
     * Layout
     *
     * @var \Magento\Framework\App\State
     * @since 2.0.0
     */
    protected $appState;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\App\AreaList $areaList
     * @param \Magento\Framework\App\State $appState
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function load()
    {
        $area = $this->_areaList->getArea($this->appState->getAreaCode());
        $area->load(\Magento\Framework\App\Area::PART_DESIGN);
        $area->load(\Magento\Framework\App\Area::PART_TRANSLATE);
        $area->detectDesign($this->_request);
    }
}
