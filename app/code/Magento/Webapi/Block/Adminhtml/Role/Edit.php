<?php
/**
 * Web API role edit page.
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @method \Magento\Webapi\Block\Adminhtml\Role\Edit setApiRole() setApiRole(\Magento\Webapi\Model\Acl\Role $role)
 * @method \Magento\Webapi\Model\Acl\Role getApiRole() getApiRole()
 */
namespace Magento\Webapi\Block\Adminhtml\Role;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * @var string
     */
    protected $_blockGroup = 'Magento_Webapi';

    /**
     * @var string
     */
    protected $_controller = 'adminhtml_role';

    /**
     * @var string
     */
    protected $_objectId = 'role_id';

    /**
     * Internal Constructor.
     */
    protected function _construct()
    {
        parent::_construct();

        // TODO: Avoid varienForm usage, it is deprecated
        $this->_formScripts[] = "function saveAndContinueEdit(url)" .
            "{var tagForm = new varienForm('edit_form'); tagForm.submit(url);}";

        $this->_addButton('save_and_continue', array(
            'label' => __('Save and Continue Edit'),
            'onclick' => "saveAndContinueEdit('" . $this->getSaveAndContinueUrl() . "')",
            'class' => 'save'
        ), 100);

        $this->_updateButton('save', 'label', __('Save API Role'));
        $this->_updateButton('delete', 'label', __('Delete API Role'));
    }

    /**
     * Retrieve role SaveAndContinue URL.
     *
     * @return string
     */
    public function getSaveAndContinueUrl()
    {
        return $this->getUrl('*/*/save', array('_current' => true, 'continue' => true));
    }

    /**
     * Get header text.
     *
     * @return string
     */
    public function getHeaderText()
    {
        if ($this->getApiRole()->getId()) {
            return __("Edit API Role '%1'", $this->escapeHtml($this->getApiRole()->getRoleName()));
        } else {
            return __('New API Role');
        }
    }
}
