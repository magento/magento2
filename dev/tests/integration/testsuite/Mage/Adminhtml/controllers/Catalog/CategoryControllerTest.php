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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Adminhtml_Catalog_CategoryControllerTest extends Mage_Adminhtml_Utility_Controller
{
    /**
     * @magentoDataFixture Mage/Core/_files/store.php
     * @magentoDbIsolation enabled
     * @dataProvider saveActionDataProvider
     * @param array $inputData
     * @param array $defaultAttributes
     * @param array $attributesSaved
     */
    public function testSaveAction($inputData, $defaultAttributes, $attributesSaved = array())
    {
        /** @var $store Mage_Core_Model_Store */
        $store = Mage::getModel('Mage_Core_Model_Store');
        $store->load('fixturestore', 'code');
        $storeId = $store->getId();

        $this->getRequest()->setPost($inputData);
        $this->getRequest()->setParam('store', $storeId);
        $this->getRequest()->setParam('id', 2);
        $this->dispatch('backend/admin/catalog_category/save');

        $messages = Mage::getSingleton('Mage_Backend_Model_Session')
            ->getMessages(false)->getItemsByType(Mage_Core_Model_Message::SUCCESS);
        $this->assertNotEmpty($messages, "Could not save category");
        $this->assertEquals('The category has been saved.', current($messages)->getCode());

        /** @var $category Mage_Catalog_Model_Category */
        $category = Mage::getModel('Mage_Catalog_Model_Category');
        $category->setStoreId($storeId);
        $category->load(2);

        $errors = array();
        foreach ($attributesSaved as $attribute => $value) {
            $actualValue = $category->getData($attribute);
            if ($value !== $actualValue) {
                $errors[] = "value for '$attribute' attribute must be '$value', but '$actualValue' is found instead";
            }
        }

        foreach ($defaultAttributes as $attribute => $exists) {
            if ($exists !== $category->getExistsStoreValueFlag($attribute)) {
                if ($exists) {
                    $errors[] = "custom value for '$attribute' attribute is not found";
                } else {
                    $errors[] = "custom value for '$attribute' attribute is found, but default one must be used";
                }
            }
        }

        $this->assertEmpty($errors, "\n" . join("\n", $errors));
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return array
     */
    public function saveActionDataProvider()
    {
        return array(
            'default values' => array(
                array(
                    'general'  => array(
                        'id'        => '2',
                        'path'      => '1/2',
                        'url_key'   => 'default-category',
                        'is_anchor' => '0',
                    ),
                    'use_default' => array(
                        0  => 'name',
                        1  => 'is_active',
                        2  => 'thumbnail',
                        3  => 'description',
                        4  => 'image',
                        5  => 'meta_title',
                        6  => 'meta_keywords',
                        7  => 'meta_description',
                        8  => 'include_in_menu',
                        9  => 'display_mode',
                        10 => 'landing_page',
                        11 => 'available_sort_by',
                        12 => 'default_sort_by',
                        13 => 'filter_price_range',
                        14 => 'custom_apply_to_products',
                        15 => 'custom_design',
                        16 => 'custom_design_from',
                        17 => 'custom_design_to',
                        18 => 'page_layout',
                        19 => 'custom_layout_update',
                    ),
                ),
                array(
                    'name'                     => false,
                    'default_sort_by'          => false,
                    'display_mode'             => false,
                    'meta_title'               => false,
                    'custom_design'            => false,
                    'page_layout'              => false,
                    'is_active'                => false,
                    'include_in_menu'          => false,
                    'landing_page'             => false,
                    'is_anchor'                => false,
                    'custom_apply_to_products' => false,
                    'available_sort_by'        => false,
                    'description'              => false,
                    'meta_keywords'            => false,
                    'meta_description'         => false,
                    'custom_layout_update'     => false,
                    'custom_design_from'       => false,
                    'custom_design_to'         => false,
                    'filter_price_range'       => false,
                ),
            ),
            'custom values'  => array(
                array(
                    'general' => array(
                        'id'                       => '2',
                        'path'                     => '1/2',
                        'name'                     => 'Custom Name',
                        'is_active'                => '0',
                        'description'              => 'Custom Description',
                        'meta_title'               => 'Custom Title',
                        'meta_keywords'            => 'Custom keywords',
                        'meta_description'         => 'Custom meta description',
                        'include_in_menu'          => '0',
                        'url_key'                  => 'default-category',
                        'display_mode'             => 'PRODUCTS',
                        'landing_page'             => '1',
                        'is_anchor'                => '1',
                        'custom_apply_to_products' => '0',
                        'custom_design'            => 'default/default/blank',
                        'custom_design_from'       => '',
                        'custom_design_to'         => '',
                        'page_layout'              => '',
                        'custom_layout_update'     => '',
                    ),
                    'use_config' => array(
                        0 => 'available_sort_by',
                        1 => 'default_sort_by',
                        2 => 'filter_price_range',
                    ),
                ),
                array(
                    'name'                     => true,
                    'default_sort_by'          => true,
                    'display_mode'             => true,
                    'meta_title'               => true,
                    'custom_design'            => true,
                    'page_layout'              => true,
                    'is_active'                => true,
                    'include_in_menu'          => true,
                    'landing_page'             => true,
                    'custom_apply_to_products' => true,
                    'available_sort_by'        => true,
                    'description'              => true,
                    'meta_keywords'            => true,
                    'meta_description'         => true,
                    'custom_layout_update'     => true,
                    'custom_design_from'       => true,
                    'custom_design_to'         => true,
                    'filter_price_range'       => true,
                ),
                array(
                    'name'                     => 'Custom Name',
                    'default_sort_by'          => NULL,
                    'display_mode'             => 'PRODUCTS',
                    'meta_title'               => 'Custom Title',
                    'custom_design'            => 'default/default/blank',
                    'page_layout'              => NULL,
                    'is_active'                => '0',
                    'include_in_menu'          => '0',
                    'landing_page'             => '1',
                    'custom_apply_to_products' => '0',
                    'available_sort_by'        => NULL,
                    'description'              => 'Custom Description',
                    'meta_keywords'            => 'Custom keywords',
                    'meta_description'         => 'Custom meta description',
                    'custom_layout_update'     => NULL,
                    'custom_design_from'       => NULL,
                    'custom_design_to'         => NULL,
                    'filter_price_range'       => NULL,
                ),
            ),
        );
    }
}
