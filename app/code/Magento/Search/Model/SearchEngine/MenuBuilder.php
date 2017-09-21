<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Model\SearchEngine;

use Magento\Backend\Model\Menu;
use Magento\Backend\Model\Menu\Builder;
use Magento\Framework\Search\SearchEngine\ConfigInterface;
use Magento\Search\Model\EngineResolver;

/**
 * A plugin for Magento\Backend\Model\Menu\Builder class. Implements "after" for "getResult()".
 *
 * The purpose of this plugin is to go through the menu tree and remove "Search Terms" menu item if the
 * selected search engine does not support "synonyms" feature.
 */
class MenuBuilder
{
    /**
     * A constant to refer to "Search Synonyms" menu item id from etc/adminhtml/menu.xml
     */
    const SEARCH_SYNONYMS_MENU_ITEM_ID = 'Magento_Search::search_synonyms';

    /**
     * @var ConfigInterface $searchFeatureConfig
     */
    protected $searchFeatureConfig;

    /**
     * @var EngineResolver $engineResolver
     */
    protected $engineResolver;

    /**
     * MenuBuilder constructor.
     *
     * @param ConfigInterface $searchFeatureConfig
     * @param EngineResolver $engineResolver
     */
    public function __construct(
        ConfigInterface $searchFeatureConfig,
        EngineResolver $engineResolver
    ) {
        $this->searchFeatureConfig = $searchFeatureConfig;
        $this->engineResolver = $engineResolver;
    }

    /**
     * Removes 'Search Synonyms' from the menu if 'synonyms' is not supported
     *
     * @param Builder $subject
     * @param Menu $menu
     * @return Menu
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetResult(Builder $subject, Menu $menu)
    {
        $searchEngine = $this->engineResolver->getCurrentSearchEngine();
        if (!$this->searchFeatureConfig
            ->isFeatureSupported(ConfigInterface::SEARCH_ENGINE_FEATURE_SYNONYMS, $searchEngine)
        ) {
            // "Search Synonyms" feature is not supported by the current configured search engine.
            // Menu will be updated to remove it from the list
            $menu->remove(self::SEARCH_SYNONYMS_MENU_ITEM_ID);
        }
        return $menu;
    }
}
