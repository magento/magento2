<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Theme\Ui\Component\Theme\DataProvider;

/**
 * Theme search result
 */
class SearchResult extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    /**
     * {@inheritdoc}
     */
    protected $_map = [
        'fields' => [
            'theme_id' => 'main_table.theme_id',
            'theme_title' => 'main_table.theme_title',
            'theme_path' => 'main_table.theme_path',
            'parent_theme_title' => 'parent.theme_title',
        ],
    ];

    /**
     * Add area and type filters
     * Join parent theme title
     *
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this
            ->addFieldToFilter('main_table.area', \Magento\Framework\App\Area::AREA_FRONTEND)
            ->addFieldToFilter('main_table.type', ['in' => [
                \Magento\Framework\View\Design\ThemeInterface::TYPE_PHYSICAL,
                \Magento\Framework\View\Design\ThemeInterface::TYPE_VIRTUAL,
            ]])
        ;

        $this->getSelect()->joinLeft(
            ['parent' => $this->getMainTable()],
            'main_table.parent_id = parent.theme_id',
            ['parent_theme_title' => 'parent.theme_title']
        );

        return $this;
    }
}
