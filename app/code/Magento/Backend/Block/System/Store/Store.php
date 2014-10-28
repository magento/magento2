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
            array(
                'label' => __('Create Store'),
                'onclick' => 'setLocation(\'' . $this->getUrl('adminhtml/*/newGroup') . '\')',
                'class' => 'add add-store'
            ),
            1
        );

        /* Add Store button */
        $this->buttonList->add(
            'add_store',
            array(
                'label' => __('Create Store View'),
                'onclick' => 'setLocation(\'' . $this->getUrl('adminhtml/*/newStore') . '\')',
                'class' => 'add add-store-view'
            )
        );
    }
}
