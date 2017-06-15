<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Config\Processor;

use Magento\Framework\App\Config\Spi\PreProcessorInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\View\Design\Theme\ListInterface;
use Magento\Framework\View\DesignInterface;

/**
 * Allows to convert configurations from \Magento\Framework\View\DesignInterface::XML_PATH_THEME_ID variables.
 */
class DesignTheme implements PreProcessorInterface
{
    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var ListInterface
     */
    private $themeList;

    /**
     * DesignTheme constructor.
     *
     * @param ArrayManager $arrayManager
     * @param ListInterface $themeList
     */
    public function __construct(
        ArrayManager $arrayManager,
        ListInterface $themeList
    ) {
        $this->arrayManager = $arrayManager;
        $this->themeList = $themeList;
    }

    /**
     * {@inheritdoc}
     */
    public function process(array $config)
    {
        foreach ($config as $scope => &$item) {
            if ($scope === \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT) {
                $item = $this->changeThemeFullPathToIdentifier($item);
            } else {
                foreach ($item as &$scopeItems) {
                    $scopeItems = $this->changeThemeFullPathToIdentifier($scopeItems);
                }
            }
        }

        return $config;
    }

    /**
     * Check \Magento\Framework\View\DesignInterface::XML_PATH_THEME_ID config path
     * and convert theme_full_path (Ex. "frontend/Magento/blank") to theme_id
     *
     * @param array $configItems
     * @return array
     */
    private function changeThemeFullPathToIdentifier($configItems)
    {
        if ($this->arrayManager->exists(DesignInterface::XML_PATH_THEME_ID, $configItems)) {
            $themeIdentifier = $this->arrayManager->get(DesignInterface::XML_PATH_THEME_ID, $configItems);
            if (!is_numeric($themeIdentifier)) {
                $theme = $this->themeList->getThemeByFullPath($themeIdentifier);
            }

            if ($theme && $theme->getId()) {
                return $this->arrayManager->set(DesignInterface::XML_PATH_THEME_ID, $configItems, $theme->getId());
            }
        }

        return $configItems;
    }
}
