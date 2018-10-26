<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Model\App;

use Magento\Framework\App\Area;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Translate\Inline\ConfigInterface;

/**
 * Emulation model
 *
 * @api
 * @since 100.0.2
 * @deprecated because additional public functionality needed to be added. Used only for backward compatibility.
 * @see \Magento\Store\Model\App\EmulationInterface
 */
class Emulation extends \Magento\Framework\DataObject
{
    /**
     * @var \Magento\Store\Model\App\EmulationInterface
     */
    private $appEmulation;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\View\DesignInterface $viewDesign
     * @param \Magento\Framework\App\DesignInterface $design
     * @param \Magento\Framework\TranslateInterface $translate
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param ConfigInterface $inlineConfig
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Psr\Log\LoggerInterface $logger
     * @param array $data
     * @param \Magento\Store\Model\App\EmulationInterface $appEmulation
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\DesignInterface $viewDesign,
        \Magento\Framework\App\DesignInterface $design,
        \Magento\Framework\TranslateInterface $translate,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        ConfigInterface $inlineConfig,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Psr\Log\LoggerInterface $logger,
        array $data = [],
        \Magento\Store\Model\App\EmulationInterface $appEmulation = null
    ) {
        parent::__construct($data);
        $this->appEmulation = $appEmulation ?? ObjectManager::getInstance()
                ->get(\Magento\Store\Model\App\EmulationInterface::class);
    }

    /**
     * Start environment emulation of the specified store
     *
     * Function returns information about initial store environment and emulates environment of another store
     *
     * @param integer $storeId
     * @param string $area
     * @param bool $force A true value will ensure that environment is always emulated, regardless of current store
     * @return void
     */
    public function startEnvironmentEmulation($storeId, $area = Area::AREA_FRONTEND, $force = false)
    {
        $this->appEmulation->startEnvironmentEmulation($storeId, $area, $force);
    }

    /**
     * Stop environment emulation
     *
     * Function restores initial store environment
     *
     * @return $this
     */
    public function stopEnvironmentEmulation()
    {
        $this->appEmulation->stopEnvironmentEmulation();

        return $this;
    }

    /**
     * Stores current environment info
     *
     * @return void
     */
    public function storeCurrentEnvironmentInfo()
    {
        $this->appEmulation->storeCurrentEnvironmentInfo();
    }
}
