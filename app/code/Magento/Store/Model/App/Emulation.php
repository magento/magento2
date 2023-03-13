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
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\DesignInterface as AppDesignInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Phrase\RendererInterface;
use Magento\Framework\Translate\Inline\ConfigInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\TranslateInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * @api
 * @since 100.0.2
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Emulation extends DataObject
{
    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var TranslateInterface
     */
    protected $_translate;

    /**
     * Core store config
     *
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @var AppDesignInterface
     */
    protected $_design;

    /**
     * Ini
     *
     * @var DataObject
     */
    private $initialEnvironmentInfo;

    /**
     * @var DesignInterface
     */
    private $_viewDesign;

    /**
     * @param StoreManagerInterface $storeManager
     * @param DesignInterface $viewDesign
     * @param AppDesignInterface $design
     * @param TranslateInterface $translate
     * @param ScopeConfigInterface $scopeConfig
     * @param ConfigInterface $inlineConfig
     * @param StateInterface $inlineTranslation
     * @param ResolverInterface $localeResolver
     * @param LoggerInterface $logger
     * @param array $data
     * @param RendererInterface|null $phraseRenderer
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        DesignInterface $viewDesign,
        AppDesignInterface $design,
        TranslateInterface $translate,
        ScopeConfigInterface $scopeConfig,
        protected readonly ConfigInterface $inlineConfig,
        protected readonly StateInterface $inlineTranslation,
        ResolverInterface $localeResolver,
        private readonly LoggerInterface $logger,
        array $data = [],
        private ?RendererInterface $phraseRenderer = null
    ) {
        $this->_localeResolver = $localeResolver;
        parent::__construct($data);
        $this->_storeManager = $storeManager;
        $this->_viewDesign = $viewDesign;
        $this->_design = $design;
        $this->_translate = $translate;
        $this->_scopeConfig = $scopeConfig;
        $this->phraseRenderer = $phraseRenderer
            ?? ObjectManager::getInstance()->get(RendererInterface::class);
    }

    /**
     * Start environment emulation of a specified store
     *
     * @param integer $storeId
     * @param string $area
     * @param bool $force A true value will ensure that environment is always emulated, regardless of current store
     * @return void
     */
    public function startEnvironmentEmulation(
        $storeId,
        $area = Area::AREA_FRONTEND,
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

        if ($area == Area::AREA_FRONTEND) {
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
        Phrase::setRenderer($this->phraseRenderer);
    }

    /**
     * Stop environment emulation
     *
     * Function restores initial store environment
     *
     * @return Emulation
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
        Phrase::setRenderer($this->initialEnvironmentInfo->getPhraseRenderer());
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
        $this->initialEnvironmentInfo = new DataObject();
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
        )->setPhraseRenderer(
            Phrase::getRenderer()
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
        $initialArea = Area::AREA_ADMINHTML
    ) {
        $this->_localeResolver->setLocale($initialLocaleCode);
        $this->_translate->setLocale($initialLocaleCode);
        $this->_translate->loadData($initialArea);

        return $this;
    }
}
