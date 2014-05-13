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
namespace Magento\Backend\Block\System\Cache;

/**
 * Cache management edit page
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Edit extends \Magento\Backend\Block\Widget
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Backend::system/cache/edit.phtml';

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setTitle('Cache Management');
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        $this->addChild(
            'save_button',
            'Magento\Backend\Block\Widget\Button',
            array(
                'label' => __('Save Cache Settings'),
                'class' => 'save',
                'data_attribute' => array(
                    'mage-init' => array('button' => array('event' => 'save', 'target' => '#config-edit-form'))
                )
            )
        );
        return parent::_prepareLayout();
    }

    /**
     * @return string
     */
    public function getSaveButtonHtml()
    {
        return $this->getChildHtml('save_button');
    }

    /**
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl('adminhtml/*/save', array('_current' => true));
    }

    /**
     * @return $this
     */
    public function initForm()
    {
        $this->setChild(
            'form',
            $this->getLayout()->createBlock('Magento\Backend\Block\System\Cache\Form')->initForm()
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
            'refresh_catalog_rewrites' => array(
                'label' => __('Catalog Rewrites'),
                'buttons' => array(array('name' => 'refresh_catalog_rewrites', 'action' => __('Refresh')))
            ),
            'clear_images_cache' => array(
                'label' => __('Images Cache'),
                'buttons' => array(array('name' => 'clear_images_cache', 'action' => __('Clear')))
            ),
            'rebuild_search_index' => array(
                'label' => __('Search Index'),
                'buttons' => array(array('name' => 'rebuild_search_index', 'action' => __('Rebuild')))
            ),
            'rebuild_inventory_stock_status' => array(
                'label' => __('Inventory Stock Status'),
                'buttons' => array(array('name' => 'rebuild_inventory_stock_status', 'action' => __('Refresh')))
            ),
            'rebuild_catalog_index' => array(
                'label' => __('Rebuild Catalog Index'),
                'buttons' => array(array('name' => 'rebuild_catalog_index', 'action' => __('Rebuild')))
            ),
            'rebuild_flat_catalog_category' => array(
                'label' => __('Rebuild Flat Catalog Category'),
                'buttons' => array(array('name' => 'rebuild_flat_catalog_category', 'action' => __('Rebuild')))
            ),
            'rebuild_flat_catalog_product' => array(
                'label' => __('Rebuild Flat Catalog Product'),
                'buttons' => array(array('name' => 'rebuild_flat_catalog_product', 'action' => __('Rebuild')))
            )
        );
    }
}
