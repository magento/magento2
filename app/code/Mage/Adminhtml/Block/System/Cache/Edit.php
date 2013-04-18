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
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Cache management edit page
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_System_Cache_Edit extends Mage_Adminhtml_Block_Widget
{

    protected $_template = 'system/cache/edit.phtml';

    protected function _construct()
    {
        parent::_construct();

        $this->setTitle('Cache Management');
    }

    protected function _prepareLayout()
    {
        $this->addChild('save_button', 'Mage_Adminhtml_Block_Widget_Button', array(
            'label'     => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Save Cache Settings'),
            'class' => 'save',
            'data_attribute'  => array(
                'mage-init' => array(
                    'button' => array('event' => 'save', 'target' => '#config-edit-form'),
                ),
            ),
        ));
        return parent::_prepareLayout();
    }

    public function getSaveButtonHtml()
    {
        return $this->getChildHtml('save_button');
    }

    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save', array('_current'=>true));
    }

    public function initForm()
    {
        $this->setChild('form',
            $this->getLayout()->createBlock('Mage_Adminhtml_Block_System_Cache_Form')
                ->initForm()
        );
        return $this;
    }

    /**
     * Retrieve Catalog Tools Data
     *
     * @return array
     */
    public function getCatalogData()
    {
        return array(
            'refresh_catalog_rewrites'   => array(
                'label'     => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Catalog Rewrites'),
                'buttons'   => array(
                    array(
                        'name'      => 'refresh_catalog_rewrites',
                        'action'    => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Refresh'),
                        )
                ),
            ),
            'clear_images_cache'         => array(
                'label'     => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Images Cache'),
                'buttons'   => array(
                    array(
                        'name'      => 'clear_images_cache',
                        'action'    => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Clear'),
                        )
                ),
            ),
            'rebuild_search_index'      => array(
                'label'     => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Search Index'),
                'buttons'   => array(
                    array(
                        'name'      => 'rebuild_search_index',
                        'action'    => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Rebuild'),
                    )
                ),
            ),
            'rebuild_inventory_stock_status' => array(
                'label'     => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Inventory Stock Status'),
                'buttons'   => array(
                    array(
                        'name'      => 'rebuild_inventory_stock_status',
                        'action'    => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Refresh'),
                    )
                ),
            ),
            'rebuild_catalog_index'         => array(
                'label'     => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Rebuild Catalog Index'),
                'buttons'   => array(
                    array(
                        'name'      => 'rebuild_catalog_index',
                        'action'    => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Rebuild'),
                    )
                ),
            ),
            'rebuild_flat_catalog_category' => array(
                'label'     => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Rebuild Flat Catalog Category'),
                'buttons'   => array(
                    array(
                        'name'      => 'rebuild_flat_catalog_category',
                        'action'    => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Rebuild'),
                    )
                ),
            ),
            'rebuild_flat_catalog_product' => array(
                'label'     => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Rebuild Flat Catalog Product'),
                'buttons'   => array(
                    array(
                        'name'      => 'rebuild_flat_catalog_product',
                        'action'    => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Rebuild'),
                    )
                ),
            ),
        );
    }
}
