<?php
/**
 * Web API Role tab with main information.
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
 * @method \Magento\Webapi\Block\Adminhtml\Role\Edit\Tab\Main setApiRole(\Magento\Webapi\Model\Acl\Role $role)
 * @method \Magento\Webapi\Model\Acl\Role getApiRole()
 *
 */
namespace Magento\Webapi\Block\Adminhtml\Role\Edit\Tab;

/**
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Main extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Prepare Form.
     *
     * @return \Magento\Backend\Block\Widget\Form
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Data\Form $form */
        $form = $this->_formFactory->create();

        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend' => __('Role Information'))
        );

        $role = $this->getApiRole();
        if ($role && $role->getId()) {
            $fieldset->addField('role_id', 'hidden', array(
                'name' => 'role_id',
                'value' => $role->getId()
            ));
        }

        $fieldset->addField('role_name', 'text', array(
            'name' => 'role_name',
            'id' => 'role_name',
            'class' => 'required-entry',
            'required' => true,
            'label' => __('Role Name'),
            'title' => __('Role Name'),
        ));

        $fieldset->addField('in_role_user', 'hidden',
            array(
                'name' => 'in_role_user',
                'id' => 'in_role_user',
            )
        );

        $fieldset->addField('in_role_user_old', 'hidden',
            array(
                'name' => 'in_role_user_old'
            )
        );

        if ($role) {
            $form->setValues($role->getData());
        }
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
