<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\System\Cache;

/**
 * Cache management edit page
 */
class Edit extends \Magento\Backend\Block\Widget
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Backend::system/cache/edit.phtml';

    /**
     * Set the page title
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setTitle('Cache Management');
    }

    /**
     * @inheritDoc
     */
    protected function _prepareLayout()
    {
        $this->addChild(
            'save_button',
            \Magento\Backend\Block\Widget\Button::class,
            [
                'label' => __('Save Cache Settings'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'save', 'target' => '#config-edit-form']],
                ]
            ]
        );
        return parent::_prepareLayout();
    }

    /**
     * Return the HTML for the save button
     *
     * @return string
     */
    public function getSaveButtonHtml()
    {
        return $this->getChildHtml('save_button');
    }

    /**
     * Return the URL to save an item
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl('adminhtml/*/save', ['_current' => true]);
    }

    /**
     * Initialise the form
     *
     * @return $this
     */
    public function initForm()
    {
        $this->setChild(
            'form',
            $this->getLayout()->createBlock(\Magento\Backend\Block\System\Cache\Form::class)->initForm()
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
        return [
            'refresh_catalog_rewrites' => [
                'label' => __('Catalog Rewrites'),
                'buttons' => [['name' => 'refresh_catalog_rewrites', 'action' => __('Refresh')]],
            ],
            'clear_images_cache' => [
                'label' => __('Images Cache'),
                'buttons' => [['name' => 'clear_images_cache', 'action' => __('Clear')]],
            ],
            'rebuild_search_index' => [
                'label' => __('Search Index'),
                'buttons' => [['name' => 'rebuild_search_index', 'action' => __('Rebuild')]],
            ],
            'rebuild_inventory_stock_status' => [
                'label' => __('Inventory Stock Status'),
                'buttons' => [['name' => 'rebuild_inventory_stock_status', 'action' => __('Refresh')]],
            ],
            'rebuild_catalog_index' => [
                'label' => __('Rebuild Catalog Index'),
                'buttons' => [['name' => 'rebuild_catalog_index', 'action' => __('Rebuild')]],
            ],
            'rebuild_flat_catalog_category' => [
                'label' => __('Rebuild Flat Catalog Category'),
                'buttons' => [['name' => 'rebuild_flat_catalog_category', 'action' => __('Rebuild')]],
            ],
            'rebuild_flat_catalog_product' => [
                'label' => __('Rebuild Flat Catalog Product'),
                'buttons' => [['name' => 'rebuild_flat_catalog_product', 'action' => __('Rebuild')]],
            ]
        ];
    }
}
