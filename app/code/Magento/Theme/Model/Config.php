<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Theme Config model
 */
namespace Magento\Theme\Model;

use Magento\Config\Model\ResourceModel\Config\Data\Collection as ResourceConfigDataCollection;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Config\ValueInterface;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Config
{
    /**
     * @var WriterInterface
     */
    protected $_configWriter;

    /**
     * @var ValueInterface
     */
    protected $_configData;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Application event manager
     *
     * @var ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var FrontendInterface
     */
    protected $_configCache;

    /**
     * @var FrontendInterface
     */
    protected $_layoutCache;

    /**
     * @param ValueInterface $configData
     * @param WriterInterface $configWriter
     * @param StoreManagerInterface $storeManager
     * @param ManagerInterface $eventManager
     * @param FrontendInterface $configCache
     * @param FrontendInterface $layoutCache
     */
    public function __construct(
        ValueInterface $configData,
        WriterInterface $configWriter,
        StoreManagerInterface $storeManager,
        ManagerInterface $eventManager,
        FrontendInterface $configCache,
        FrontendInterface $layoutCache
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
     * @param ThemeInterface $theme
     * @param array $stores
     * @param string $scope
     * @return $this
     */
    public function assignToStore(
        $theme,
        array $stores = [],
        $scope = ScopeInterface::SCOPE_STORES
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
     * @return ResourceConfigDataCollection
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
     */
    protected function _unassignThemeFromStores($themeId, $stores, $scope, &$isReassigned)
    {
        $configPath = DesignInterface::XML_PATH_THEME_ID;
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
     */
    protected function _assignThemeToStores($themeId, $stores, $scope, &$isReassigned)
    {
        $configPath = DesignInterface::XML_PATH_THEME_ID;
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
     */
    protected function _assignThemeToDefaultScope($themeId, &$isReassigned)
    {
        $configPath = DesignInterface::XML_PATH_THEME_ID;
        $this->_configWriter->save($configPath, $themeId, ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
        $isReassigned = true;
        return $this;
    }
}
