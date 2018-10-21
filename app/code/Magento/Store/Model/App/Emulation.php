<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Emulation model
 */
namespace Magento\Store\Model\App;

use Magento\Framework\App\Area;
use Magento\Framework\App\ObjectManager;

/**
 * @api
 * @since 100.0.2
 * @deprecated
 * @see \Magento\Store\Model\App\EnvironmentEmulation
 */
class Emulation extends \Magento\Framework\DataObject implements \Magento\Store\Model\App\EmulationInterface
{
    /**
     * @var \Magento\Store\Model\App\EnvironmentEmulation
     */
    private $appEmulation;

    public function __construct(
        \Magento\Store\Model\App\EnvironmentEmulation $appEmulation = null
    ) {
        parent::__construct();
        $this->appEmulation = $appEmulation ?? ObjectManager::getInstance()
                ->get(\Magento\Store\Model\App\EnvironmentEmulation::class);
    }

    /**
     * {@inheritdoc}
     */
    public function startEnvironmentEmulation($storeId, $area = Area::AREA_FRONTEND, $force = false)
    {
        $this->appEmulation->startEnvironmentEmulation($storeId, $area, $force);
    }

    /**
     * {@inheritdoc}
     */
    public function stopEnvironmentEmulation()
    {
        $this->appEmulation->stopEnvironmentEmulation();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function storeCurrentEnvironmentInfo()
    {
        $this->appEmulation->storeCurrentEnvironmentInfo();
    }
}
