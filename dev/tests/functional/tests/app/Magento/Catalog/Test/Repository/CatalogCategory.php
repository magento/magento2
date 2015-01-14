<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Repository;

use Mtf\Repository\AbstractRepository;

/**
 * Class CatalogCategory
 * Data for creation Category
 */
class CatalogCategory extends AbstractRepository
{
    /**
     * @constructor
     * @param array $defaultConfig
     * @param array $defaultData
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(array $defaultConfig = [], array $defaultData = [])
    {
        $this->_data['default_category'] = [
            'name' => 'Default Category',
            'parent_id' => 1,
            'is_active' => 'Yes',
            'id' => 2,
        ];

        $this->_data['default_subcategory'] = [
            'name' => 'DefaultSubcategory%isolation%',
            'url_key' => 'default-subcategory-%isolation%',
            'parent_id' => ['dataSet' => 'default_category'],
            'is_active' => 'Yes',
            'include_in_menu' => 'Yes',
        ];

        $this->_data['default_anchor_subcategory'] = [
            'name' => 'DefaultSubcategory%isolation%',
            'url_key' => 'default-subcategory-%isolation%',
            'parent_id' => ['dataSet' => 'default_category'],
            'is_active' => 'Yes',
            'is_anchor' => 'Yes',
            'include_in_menu' => 'Yes',
        ];

        $this->_data['root_category'] = [
            'name' => 'RootCategory%isolation%',
            'url_key' => 'root-category-%isolation%',
            'parent_id' => 1,
            'is_active' => 'Yes',
            'include_in_menu' => 'Yes',
        ];

        $this->_data['root_subcategory'] = [
            'name' => 'RootSubCategory%isolation%',
            'url_key' => 'root-sub-category-%isolation%',
            'parent_id' => ['dataSet' => 'root_category'],
            'is_active' => 'Yes',
            'include_in_menu' => 'Yes',
        ];
    }
}
