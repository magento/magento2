<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Design\Config\Plugin;

use Magento\Config\App\Config\Source\DumpConfigSourceAggregated;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\View\Design\Theme\ListInterface;
use Magento\Framework\View\DesignInterface;

/**
 * This is plugin for Magento\Config\App\Config\Source\DumpConfigSourceAggregated class.
 *
 * Detects the design theme configuration data (path \Magento\Framework\View\DesignInterface::XML_PATH_THEME_ID)
 * and convert theme identifier from theme_id to theme_full_path.
 * As a result of Magento\Config\App\Config\Source\DumpConfigSourceAggregated expected
 * to be shared between environments where IDs can not be used, we need
 * to change theme id to full path value what can be used as an identifier.
 * @see \Magento\Config\App\Config\Source\DumpConfigSourceAggregated
 * @since 2.2.0
 */
class Dump
{
    /**
     * @var ListInterface
     * @since 2.2.0
     */
    private $themeList;

    /**
     * @var ArrayManager
     * @since 2.2.0
     */
    private $arrayManager;

    /**
     * @param ListInterface $themeList
     * @param ArrayManager $arrayManager
     * @since 2.2.0
     */
    public function __construct(
        ListInterface $themeList,
        ArrayManager $arrayManager
    ) {
        $this->themeList = $themeList;
        $this->arrayManager = $arrayManager;
    }

    /**
     * Change value from theme_id field to full path for every existed scope.
     * All other values leave without changes.
     *
     * @param DumpConfigSourceAggregated $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.2.0
     */
    public function afterGet(DumpConfigSourceAggregated $subject, $result)
    {
        foreach ($result as $scope => &$item) {
            if ($scope === \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT) {
                $item = $this->changeThemeIdToFullPath($item);
            } else {
                foreach ($item as &$scopeItems) {
                    $scopeItems = $this->changeThemeIdToFullPath($scopeItems);
                }
            }
        }

        return $result;
    }

    /**
     * Check \Magento\Framework\View\DesignInterface::XML_PATH_THEME_ID config path
     * and convert theme_id to full_theme_path. Ex. "frontend/Magento/blank"
     *
     * @param array $configItems
     * @return array
     * @since 2.2.0
     */
    private function changeThemeIdToFullPath($configItems)
    {
        $theme = null;
        if ($this->arrayManager->exists(DesignInterface::XML_PATH_THEME_ID, $configItems)) {
            $themeIdentifier = $this->arrayManager->get(DesignInterface::XML_PATH_THEME_ID, $configItems);
            if (is_numeric($themeIdentifier)) {
                $theme = $this->themeList->getItemById($themeIdentifier);
            }

            if ($theme && $theme->getFullPath()) {
                return $this->arrayManager->set(
                    DesignInterface::XML_PATH_THEME_ID,
                    $configItems,
                    $theme->getFullPath()
                );
            }
        }

        return $configItems;
    }
}
