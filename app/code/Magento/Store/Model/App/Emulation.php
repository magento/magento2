<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Emulation model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Store\Model\App;

use Magento\Framework\Translate\Inline\ConfigInterface;

class Emulation extends \Magento\Framework\DataObject
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\TranslateInterface
     */
    protected $_translate;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @var \Magento\Framework\App\DesignInterface
     */
    protected $_design;

    /**
     * @var ConfigInterface
     */
    protected $inlineConfig;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * Ini
     *
     * @var \Magento\Framework\DataObject
     */
    private $initialEnvironmentInfo;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

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
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
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
        array $data = []
    ) {
        $this->_localeResolver = $localeResolver;
        parent::__construct($data);
        $this->_storeManager = $storeManager;
        $this->_viewDesign = $viewDesign;
        $this->_design = $design;
        $this->_translate = $translate;
        $this->_scopeConfig = $scopeConfig;
        $this->inlineConfig = $inlineConfig;
        $this->inlineTranslation = $inlineTranslation;
        $this->logger = $logger;
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
    public function startEnvironmentEmulation(
        $storeId,
        $area = \Magento\Framework\App\Area::AREA_FRONTEND,
        $force = false
    ) {
        // Only allow a single level of emulation
        if ($this->initialEnvironmentInfo !== null) {
            $this->logger->error(__('Environment emulation nesting is not allowed.'));
            return;
        }

        if ($storeId == $this->_storeManager->getStore()->getStoreId() && !$force) {
            return;
        }
        $this->storeCurrentEnvironmentInfo();

        // emulate inline translations
        $this->inlineTranslation->suspend($this->inlineConfig->isActive($storeId));

        // emulate design
        $storeTheme = $this->_viewDesign->getConfigurationDesignTheme($area, ['store' => $storeId]);
        $this->_viewDesign->setDesignTheme($storeTheme, $area);

        if ($area == \Magento\Framework\App\Area::AREA_FRONTEND) {
            $designChange = $this->_design->loadChange($storeId);
            if ($designChange->getData()) {
                $this->_viewDesign->setDesignTheme($designChange->getDesign(), $area);
            }
        }

        // Current store needs to be changed right before locale change and after design change
        $this->_storeManager->setCurrentStore($storeId);

        // emulate locale
        $newLocaleCode = $this->_scopeConfig->getValue(
            $this->_localeResolver->getDefaultLocalePath(),
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $this->_localeResolver->setLocale($newLocaleCode);
        $this->_translate->setLocale($newLocaleCode);
        $this->_translate->loadData($area);

        return;
    }

    /**
     * Stop environment emulation
     *
     * Function restores initial store environment
     *
     * @return \Magento\Store\Model\App\Emulation
     */
    public function stopEnvironmentEmulation()
    {
        if ($this->initialEnvironmentInfo === null) {
            return $this;
        }

        $this->_restoreInitialInlineTranslation($this->initialEnvironmentInfo->getInitialTranslateInline());
        $initialDesign = $this->initialEnvironmentInfo->getInitialDesign();
        $this->_restoreInitialDesign($initialDesign);
        // Current store needs to be changed right before locale change and after design change
        $this->_storeManager->setCurrentStore($initialDesign['store']);
        $this->_restoreInitialLocale($this->initialEnvironmentInfo->getInitialLocaleCode(), $initialDesign['area']);

        $this->initialEnvironmentInfo = null;
        return $this;
    }

    /**
     * Stores current environment info
     *
     * @return void
     */
    public function storeCurrentEnvironmentInfo()
    {
        $this->initialEnvironmentInfo = new \Magento\Framework\DataObject();
        $this->initialEnvironmentInfo->setInitialTranslateInline(
            $this->inlineTranslation->isEnabled()
        )->setInitialDesign(
            [
                'area' => $this->_viewDesign->getArea(),
                'theme' => $this->_viewDesign->getDesignTheme(),
                'store' => $this->_storeManager->getStore()->getStoreId(),
            ]
        )->setInitialLocaleCode(
            $this->_localeResolver->getLocale()
        );
    }

    /**
     * Restore initial inline translation state
     *
     * @param bool $initialTranslate
     * @return $this
     */
    protected function _restoreInitialInlineTranslation($initialTranslate)
    {
        $this->inlineTranslation->resume($initialTranslate);
        return $this;
    }

    /**
     * Restore design of the initial store
     *
     * @param array $initialDesign
     * @return $this
     */
    protected function _restoreInitialDesign(array $initialDesign)
    {
        $this->_viewDesign->setDesignTheme($initialDesign['theme'], $initialDesign['area']);
        return $this;
    }

    /**
     * Restore locale of the initial store
     *
     * @param string $initialLocaleCode
     * @param string $initialArea
     * @return $this
     */
    protected function _restoreInitialLocale(
        $initialLocaleCode,
        $initialArea = \Magento\Framework\App\Area::AREA_ADMIN
    ) {
        $this->_localeResolver->setLocale($initialLocaleCode);
        $this->_translate->setLocale($initialLocaleCode);
        $this->_translate->loadData($initialArea);

        return $this;
    }
}
