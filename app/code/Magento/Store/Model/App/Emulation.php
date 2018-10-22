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
use Magento\Framework\Translate\Inline\ConfigInterface;

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
     * @param \Magento\Store\Model\App\EnvironmentEmulation $appEmulation
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
        \Magento\Store\Model\App\EnvironmentEmulation $appEmulation = null
    ) {
        parent::__construct($data);
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
