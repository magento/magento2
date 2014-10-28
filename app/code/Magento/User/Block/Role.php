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
namespace Magento\User\Block;

/**
 * Magento_User role block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Role extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @var string
     */
    protected $_controller = 'user_role';

    /**
     * @var string
     */
    protected $_blockGroup = 'Magento_User';

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_headerText = __('Roles');
        $this->_addButtonLabel = __('Add New Role');
        parent::_construct();
    }

    /**
     * @return string
     */
    public function getCreateUrl()
    {
        return $this->getUrl('*/*/editrole');
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        if (!$this->getLayout()->getChildName($this->getNameInLayout(), 'grid')) {
            $this->setChild(
                'grid',
                $this->getLayout()->createBlock(
                    $this->_blockGroup . '\\Block\\Role\\Grid',
                    $this->_controller . '.grid'
                )->setSaveParametersInSession(
                    true
                )
            );
        }
        return \Magento\Backend\Block\Widget\Container::_prepareLayout();
    }

    /**
     * Prepare output HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        $this->_eventManager->dispatch('permissions_role_html_before', array('block' => $this));
        return parent::_toHtml();
    }
}
