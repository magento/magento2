<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\StoreGraphQl\Plugin;

use Magento\Framework\App\AreaInterface;
use Magento\Framework\App\AreaList;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Emulate the correct store when GraphQL is sending an email
 */
class LocalizeEmail
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Emulation
     */
    private $emulation;

    /**
     * @var AreaList
     */
    private $areaList;

    /**
     * @var State
     */
    private $appState;

    /**
     * @param StoreManagerInterface $storeManager
     * @param Emulation $emulation
     * @param AreaList $areaList
     * @param State $appState
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Emulation $emulation,
        AreaList $areaList,
        State $appState
    ) {
        $this->storeManager = $storeManager;
        $this->emulation = $emulation;
        $this->areaList = $areaList;
        $this->appState = $appState;
    }

    /**
     * Emulate the correct store during email preparation
     *
     * @param TransportBuilder $subject
     * @param \Closure $proceed
     * @return mixed
     * @throws NoSuchEntityException|LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetTransport(TransportBuilder $subject, \Closure $proceed)
    {
        // Load translations for the app
        $area = $this->areaList->getArea($this->appState->getAreaCode());
        $area->load(AreaInterface::PART_TRANSLATE);

        $currentStore = $this->storeManager->getStore();
        $this->emulation->startEnvironmentEmulation($currentStore->getId());
        $output = $proceed();
        $this->emulation->stopEnvironmentEmulation();

        return $output;
    }
}
