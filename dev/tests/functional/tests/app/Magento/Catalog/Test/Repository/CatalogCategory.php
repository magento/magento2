<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
            'include_in_menu' => 'Yes'
        ];

        $this->_data['root_subcategory'] = [
            'name' => 'RootSubCategory%isolation%',
            'url_key' => 'root-sub-category-%isolation%',
            'parent_id' => ['dataSet' => 'root_category'],
            'is_active' => 'Yes',
            'include_in_menu' => 'Yes'
        ];
    }
}
