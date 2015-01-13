<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\System\Store;

/**
 * Adminhtml store content block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Store extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @var string
     */
    protected $_blockGroup = 'Magento_Adminhtml';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Magento_Backend';
        $this->_controller = 'system_store';
        $this->_headerText = __('Stores');
        parent::_construct();

        /* Update default add button to add website button */
        $this->buttonList->update('add', 'label', __('Create Website'));
        $this->buttonList->update('add', 'onclick', "setLocation('" . $this->getUrl('adminhtml/*/newWebsite') . "')");

        /* Add Store Group button */
        $this->buttonList->add(
            'add_group',
            [
                'label' => __('Create Store'),
                'onclick' => 'setLocation(\'' . $this->getUrl('adminhtml/*/newGroup') . '\')',
                'class' => 'add add-store'
            ],
            1
        );

        /* Add Store button */
        $this->buttonList->add(
            'add_store',
            [
                'label' => __('Create Store View'),
                'onclick' => 'setLocation(\'' . $this->getUrl('adminhtml/*/newStore') . '\')',
                'class' => 'add add-store-view'
            ]
        );
    }
}
