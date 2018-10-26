<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Model\App;

use \Magento\Framework\App\Area;

/**
 * Environment emulation model
 */
class EnvironmentEmulation extends \Magento\Framework\DataObject implements \Magento\Store\Model\App\EmulationInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\TranslateInterface
     */
    private $translate;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    private $localeResolver;

    /**
     * @var \Magento\Framework\App\DesignInterface
     */
    private $design;

    /**
     * @var \Magento\Framework\View\DesignInterface
     */
    private $viewDesign;

    /**
     * @var \Magento\Framework\Translate\Inline\ConfigInterface
     */
    private $inlineConfig;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    private $inlineTranslation;

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
     * @param \Magento\Framework\Translate\Inline\ConfigInterface $inlineConfig
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
        \Magento\Framework\Translate\Inline\ConfigInterface $inlineConfig,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Psr\Log\LoggerInterface $logger,
        array $data = []
    ) {
        parent::__construct($data);
        $this->localeResolver = $localeResolver;
        $this->storeManager = $storeManager;
        $this->viewDesign = $viewDesign;
        $this->design = $design;
        $this->translate = $translate;
        $this->scopeConfig = $scopeConfig;
        $this->inlineConfig = $inlineConfig;
        $this->inlineTranslation = $inlineTranslation;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function startEnvironmentEmulation($storeId, $area = Area::AREA_FRONTEND, $force = false)
    {
        // Only allow a single level of emulation
        if ($this->isEnvironmentEmulated()) {
            $this->logger->error(__('Environment emulation nesting is not allowed.'));
            return;
        }

        if ($storeId == $this->storeManager->getStore()->getStoreId() && !$force) {
            return;
        }
        $this->storeCurrentEnvironmentInfo();

        // emulate inline translations
        $this->inlineTranslation->suspend($this->inlineConfig->isActive($storeId));

        // emulate design
        $storeTheme = $this->viewDesign->getConfigurationDesignTheme($area, ['store' => $storeId]);
        $this->viewDesign->setDesignTheme($storeTheme, $area);

        if ($area == \Magento\Framework\App\Area::AREA_FRONTEND) {
            $designChange = $this->design->loadChange($storeId);
            if ($designChange->getData()) {
                $this->viewDesign->setDesignTheme($designChange->getDesign(), $area);
            }
        }

        // Current store needs to be changed right before locale change and after design change
        $this->storeManager->setCurrentStore($storeId);

        // emulate locale
        $newLocaleCode = $this->scopeConfig->getValue(
            $this->localeResolver->getDefaultLocalePath(),
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $this->localeResolver->setLocale($newLocaleCode);
        $this->translate->setLocale($newLocaleCode);
        $this->translate->loadData($area);
    }

    /**
     * {@inheritdoc}
     */
    public function stopEnvironmentEmulation()
    {
        if (!$this->isEnvironmentEmulated()) {
            return $this;
        }

        $this->restoreInitialInlineTranslation($this->initialEnvironmentInfo->getInitialTranslateInline());
        $initialDesign = $this->initialEnvironmentInfo->getInitialDesign();
        $this->restoreInitialDesign($initialDesign);
        // Current store needs to be changed right before locale change and after design change
        $this->storeManager->setCurrentStore($initialDesign['store']);
        $this->restoreInitialLocale($this->initialEnvironmentInfo->getInitialLocaleCode(), $initialDesign['area']);

        $this->initialEnvironmentInfo = null;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function storeCurrentEnvironmentInfo()
    {
        $this->initialEnvironmentInfo = new \Magento\Framework\DataObject();
        $this->initialEnvironmentInfo->setInitialTranslateInline(
            $this->inlineTranslation->isEnabled()
        )->setInitialDesign(
            [
                'area' => $this->viewDesign->getArea(),
                'theme' => $this->viewDesign->getDesignTheme(),
                'store' => $this->storeManager->getStore()->getStoreId(),
            ]
        )->setInitialLocaleCode(
            $this->localeResolver->getLocale()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function isEnvironmentEmulated()
    {
        return $this->initialEnvironmentInfo !== null;
    }

    /**
     * Restore initial inline translation state
     *
     * @param bool $initialTranslate
     * @return $this
     */
    private function restoreInitialInlineTranslation($initialTranslate)
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
    private function restoreInitialDesign(array $initialDesign)
    {
        $this->viewDesign->setDesignTheme($initialDesign['theme'], $initialDesign['area']);
        return $this;
    }

    /**
     * Restore locale of the initial store
     *
     * @param string $initialLocaleCode
     * @param string $initialArea
     * @return $this
     */
    private function restoreInitialLocale(
        $initialLocaleCode,
        $initialArea = \Magento\Framework\App\Area::AREA_ADMINHTML
    ) {
        $this->localeResolver->setLocale($initialLocaleCode);
        $this->translate->setLocale($initialLocaleCode);
        $this->translate->loadData($initialArea);

        return $this;
    }
}
