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

namespace Magento\Catalog\Test\Fixture;

use Mtf\Factory\Factory;
use Mtf\Fixture\DataFixture;
use Mtf\System\Config;

/**
 * Class Category
 *
 */
class Category extends DataFixture
{
    /**
     * Attribute set for mapping data into ui tabs
     */
    const GROUP_GENERAL_INFORMATION = 'general_information';
    const GROUP_DISPLAY_SETTINGS = 'display_setting';

    /**
     * Contains categories that are needed to create next category
     *
     * @var array
     */
    protected $_categories;

    /**
     * Custom constructor to create category with custom parent category
     *
     * @param Config $configuration
     * @param array $placeholders
     */
    public function __construct(Config $configuration, $placeholders = array())
    {
        parent::__construct($configuration, $placeholders);

        $this->_placeholders['men::getCategoryName'] = array($this, '_categoryProvider');
        $this->_placeholders['men::getCategoryId'] = array($this, '_categoryProvider');
    }

    /**
     * Create category needed for placeholders in data and call method for placeholder
     *
     * @param string $placeholder
     * @return string
     */
    protected function _categoryProvider($placeholder)
    {
        list($key, $method) = explode('::', $placeholder);
        if (!isset($this->_categories[$key])) {
            $category = Factory::getFixtureFactory()->getMagentoCatalogCategory();
            $category->switchData($key);
            $category->persist();
            $this->_categories[$key] = $category;
        }

        return is_callable(array($this->_categories[$key], $method)) ? $this->_categories[$key]->$method() : '';
    }

    /**
     * Get product name
     *
     * @return string
     */
    public function getCategoryName()
    {
        return $this->getData('fields/name/value');
    }


    /**
     * Get product name
     *
     * @return string
     */
    public function getCategoryId()
    {
        return $this->getData('fields/category_id/value');
    }

    /**
     * Create category
     *
     * @return Category
     */
    public function persist()
    {
        $id = Factory::getApp()->magentoCatalogCreateCategory($this);
        $this->_data['fields']['category_id']['value'] = $id;
        return $this;
    }

    /**
     * {inheritdoc}
     */
    protected function _initData()
    {
        $this->_dataConfig = array(
            'constraint' => 'Success',
            'request_params' => array(
                'store' => '0'
            ),
            'input_prefix' => 'general'
        );

        $this->_data = array(
            'fields' => array(
                'name' => array(
                    'value' => 'Subcategory %isolation%',
                    'group' => static::GROUP_GENERAL_INFORMATION
                ),
                'is_active' => array(
                    'value' => 'Yes',
                    'input_value' => '1',
                    'group' => static::GROUP_GENERAL_INFORMATION,
                    'input' => 'select'
                ),
                'include_in_menu' => array(
                    'value' => 'Yes',
                    'input_value' => '1',
                    'group' => static::GROUP_GENERAL_INFORMATION,
                    'input' => 'select'
                ),
                'use_config_group_5available_sort_by' => array(
                    'value' => '',
                    'input_name' => 'use_config[0]',
                    'input_value' => 'available_sort_by',
                    'group' => static::GROUP_DISPLAY_SETTINGS,
                    'input' => 'checkbox'
                ),
                'use_config_group_5default_sort_by' => array(
                    'value' => '',
                    'input_name' => 'use_config[1]',
                    'input_value' => 'default_sort_by',
                    'group' => static::GROUP_DISPLAY_SETTINGS,
                    'input' => 'checkbox'
                )
            ),
            'category_path' =>  array(
                'value' => 'Default Category',
                'input_value' => '2'
            )
        );

        $this->_repository = Factory::getRepositoryFactory()
            ->getMagentoCatalogCategory($this->_dataConfig, $this->_data);
    }

    /**
     * Get path where to create new category
     *
     * @return string
     */
    public function getCategoryPath()
    {
        return $this->getData('category_path/value');
    }
}
