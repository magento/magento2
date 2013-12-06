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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Integration\Block\Adminhtml\Integration;

/**
 * Main Integration properties edit form
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Token extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Set form id prefix, declare fields for integration consumer modal
     *
     * @return \Magento\Integration\Block\Adminhtml\Integration\Token
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Data\Form $form */
        $form = $this->_formFactory->create();
        $htmlIdPrefix = 'integration_token_';
        $form->setHtmlIdPrefix($htmlIdPrefix);

        $fieldset = $form->addFieldset('base_fieldset', array(
                    'legend'    =>  __('Integration Tokens for Extensions'),
                    'class'    =>  'fieldset-wide'
                ));

        $fieldset->addField('token', 'text', array(
            'label'     => __('Token'),
            'name'      => 'token',
            'readonly'  => true
        ));

        $fieldset->addField('token-secret', 'text', array(
            'label'     => __('Token Secret'),
            'name'      => 'token-secret',
            'readonly'  => true
        ));

        $fieldset->addField('client-id', 'text', array(
            'label'     => __('Client ID'),
            'name'      => 'client-id',
            'readonly'  => true
        ));

        $fieldset->addField('client-secret', 'text', array(
            'label'     => __('Client Secret'),
            'name'      => 'client-secret',
            'readonly'  => true
        ));

        // TODO: retrieve token associated to this integration to populate the form
        // $form->setValues($model->getData());

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
