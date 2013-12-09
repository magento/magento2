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
 * @category    Magento
 * @package     Magento_Integration
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Integration\Block\Adminhtml\Integration\Edit\Tab;

use \Magento\Integration\Controller\Adminhtml\Integration;

/**
 * Main Integration info edit form
 *
 * @category   Magento
 * @package    Magento_Integration
 */
class Info extends \Magento\Backend\Block\Widget\Form\Generic
    implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**#@+
     * edit_form element names.
     */
    const HTML_ID_PREFIX = 'integration_properties_';
    const DATA_ID = 'integration_id';
    const DATA_NAME = 'name';
    const DATA_EMAIL = 'email';
    const DATA_ENDPOINT = 'endpoint';
    const DATA_SETUP_TYPE = 'setup_type';
    /**#@-*/

    /**
     * Set form id prefix, declare fields for integration info
     *
     * @return \Magento\Integration\Block\Adminhtml\Integration\Edit\Tab\Info
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix(self::HTML_ID_PREFIX);
        $integrationData = $this->_coreRegistry->registry(Integration::REGISTRY_KEY_CURRENT_INTEGRATION);
        $fieldset = $form->addFieldset('base_fieldset', array('legend' => __('General')));
        if (isset($integrationData[self::DATA_ID])) {
            $fieldset->addField(self::DATA_ID, 'hidden', array('name' => 'id'));
        }
        $fieldset->addField(
            self::DATA_NAME,
            'text',
            array(
                'label' => __('Name'),
                'name' => self::DATA_NAME,
                'required' => true,
                'disabled' => false,
                'maxlength' => '255'
            )
        );
        $fieldset->addField(
            self::DATA_EMAIL,
            'text',
            array(
                'label' => __('Email'),
                'name' => self::DATA_EMAIL,
                'disabled' => false,
                'class' => 'validate-email',
                'maxlength' => '254'
            )
        );
        $fieldset->addField(
            self::DATA_ENDPOINT,
            'text',
            array(
                'label' => __('Callback URL'),
                'name' => self::DATA_ENDPOINT,
                'disabled' => false,
                // @codingStandardsIgnoreStart
                'note'=> __('When using Oauth for token exchange, enter URL where Oauth credentials can be POST-ed. We strongly recommend you to use https://')
                // @codingStandardsIgnoreEnd
            )
        );
        $form->setValues($integrationData);
        $this->setForm($form);
        return $this;
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Integration Info');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Returns status flag about this tab can be showen or not
     *
     * @return true
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return true
     */
    public function isHidden()
    {
        return false;
    }
}
