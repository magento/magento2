<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Theme Config model
 */
namespace Magento\Theme\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class \Magento\Theme\Model\Config
 *
 * @since 2.0.0
 */
class Config
{
    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     * @since 2.0.0
     */
    protected $_configWriter;

    /**
     * @var \Magento\Framework\App\Config\ValueInterface
     * @since 2.0.0
     */
    protected $_configData;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $_storeManager;

    /**
     * Application event manager
     *
     * @var \Magento\Framework\Event\ManagerInterface
     * @since 2.0.0
     */
    protected $_eventManager;

    /**
     * @var \Magento\Framework\Cache\FrontendInterface
     * @since 2.0.0
     */
    protected $_configCache;

    /**
     * @var \Magento\Framework\Cache\FrontendInterface
     * @since 2.0.0
     */
    protected $_layoutCache;

    /**
     * @param \Magento\Framework\App\Config\ValueInterface $configData
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Cache\FrontendInterface $configCache
     * @param \Magento\Framework\Cache\FrontendInterface $layoutCache
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\App\Config\ValueInterface $configData,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Cache\FrontendInterface $configCache,
        \Magento\Framework\Cache\FrontendInterface $layoutCache
    ) {
        $this->_configData = $configData;
        $this->_configWriter = $configWriter;
        $this->_storeManager = $storeManager;
        $this->_eventManager = $eventManager;
        $this->_configCache = $configCache;
        $this->_layoutCache = $layoutCache;
    }

    /**
     * Assign theme to the stores
     *
     * @param \Magento\Framework\View\Design\ThemeInterface $theme
     * @param array $stores
     * @param string $scope
     * @return $this
     * @since 2.0.0
     */
    public function assignToStore(
        $theme,
        array $stores = [],
        $scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES
    ) {
        $isReassigned = false;

        $this->_unassignThemeFromStores($theme->getId(), $stores, $scope, $isReassigned);

        if ($this->_storeManager->isSingleStoreMode()) {
            $this->_assignThemeToDefaultScope($theme->getId(), $isReassigned);
        } else {
            $this->_assignThemeToStores($theme->getId(), $stores, $scope, $isReassigned);
        }

        if ($isReassigned) {
            $this->_configCache->clean();
            $this->_layoutCache->clean();
        }

        $this->_eventManager->dispatch(
            'assign_theme_to_stores_after',
            ['stores' => $stores, 'scope' => $scope, 'theme' => $theme]
        );

        return $this;
    }

    /**
     * Get assigned scopes collection of a theme
     *
     * @param string $scope
     * @param string $configPath
     * @return \Magento\Config\Model\ResourceModel\Config\Data\Collection
     * @since 2.0.0
     */
    protected function _getAssignedScopesCollection($scope, $configPath)
    {
        return $this->_configData->getCollection()->addFieldToFilter(
            'scope',
            $scope
        )->addFieldToFilter(
            'path',
            $configPath
        );
    }

    /**
     * Unassign given theme from stores that were unchecked
     *
     * @param string $themeId
     * @param array $stores
     * @param string $scope
     * @param bool &$isReassigned
     * @return $this
     * @since 2.0.0
     */
    protected function _unassignThemeFromStores($themeId, $stores, $scope, &$isReassigned)
    {
        $configPath = \Magento\Framework\View\DesignInterface::XML_PATH_THEME_ID;
        foreach ($this->_getAssignedScopesCollection($scope, $configPath) as $config) {
            if ($config->getValue() == $themeId && !in_array($config->getScopeId(), $stores)) {
                $this->_configWriter->delete($configPath, $scope, $config->getScopeId());
                $isReassigned = true;
            }
        }
        return $this;
    }

    /**
     * Assign given theme to stores
     *
     * @param string $themeId
     * @param array $stores
     * @param string $scope
     * @param bool &$isReassigned
     * @return $this
     * @since 2.0.0
     */
    protected function _assignThemeToStores($themeId, $stores, $scope, &$isReassigned)
    {
        $configPath = \Magento\Framework\View\DesignInterface::XML_PATH_THEME_ID;
        if (count($stores) > 0) {
            foreach ($stores as $storeId) {
                $this->_configWriter->save($configPath, $themeId, $scope, $storeId);
                $isReassigned = true;
            }
        }
        return $this;
    }

    /**
     * Assign theme to default scope
     *
     * @param string $themeId
     * @param bool &$isReassigned
     * @return $this
     * @since 2.0.0
     */
    protected function _assignThemeToDefaultScope($themeId, &$isReassigned)
    {
        $configPath = \Magento\Framework\View\DesignInterface::XML_PATH_THEME_ID;
        $this->_configWriter->save($configPath, $themeId, ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
        $isReassigned = true;
        return $this;
    }
}
