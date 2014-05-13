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
namespace Magento\User\Block\Role\Tab;

/**
 * implementing now
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Info extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @return string
     */
    public function getTabLabel()
    {
        return __('Role Info');
    }

    /**
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return $this
     */
    public function _beforeToHtml()
    {
        $this->_initForm();

        return parent::_beforeToHtml();
    }

    /**
     * @return void
     */
    protected function _initForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $fieldset = $form->addFieldset('base_fieldset', array('legend' => __('Role Information')));

        $fieldset->addField(
            'role_name',
            'text',
            array(
                'name' => 'rolename',
                'label' => __('Role Name'),
                'id' => 'role_name',
                'class' => 'required-entry',
                'required' => true
            )
        );

        $fieldset->addField('role_id', 'hidden', array('name' => 'role_id', 'id' => 'role_id'));

        $fieldset->addField('in_role_user', 'hidden', array('name' => 'in_role_user', 'id' => 'in_role_userz'));

        $fieldset->addField('in_role_user_old', 'hidden', array('name' => 'in_role_user_old'));

        $form->setValues($this->getRole()->getData());
        $this->setForm($form);
    }
}
