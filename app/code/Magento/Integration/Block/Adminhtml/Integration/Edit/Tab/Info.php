<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Integration\Block\Adminhtml\Integration\Edit\Tab;

use Magento\Integration\Controller\Adminhtml\Integration;
use Magento\Integration\Model\Integration as IntegrationModel;

/**
 * Main Integration info edit form
 *
 * @api
 * @since 100.0.2
 */
class Info extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**#@+
     * Form elements names.
     */
    public const HTML_ID_PREFIX = 'integration_properties_';

    public const DATA_ID = 'integration_id';

    public const DATA_NAME = 'name';

    public const DATA_EMAIL = 'email';

    public const DATA_ENDPOINT = 'endpoint';

    public const DATA_IDENTITY_LINK_URL = 'identity_link_url';

    public const DATA_SETUP_TYPE = 'setup_type';

    public const DATA_CONSUMER_ID = 'consumer_id';

    public const DATA_CONSUMER_PASSWORD = 'current_password';

    /**#@-*/

    /**
     * Set form id prefix, declare fields for integration info
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix(self::HTML_ID_PREFIX);
        $integrationData = $this->_coreRegistry->registry(Integration::REGISTRY_KEY_CURRENT_INTEGRATION);
        $this->_addGeneralFieldset($form, $integrationData);
        $this->_addDetailsFieldset($form, $integrationData);
        $form->setValues($integrationData);
        $this->setForm($form);
        return $this;
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
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
     * Returns status flag about this tab can be shown or not
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

    /**
     * Add fieldset with general integration information.
     *
     * @param \Magento\Framework\Data\Form $form
     * @param array $integrationData
     * @return void
     */
    protected function _addGeneralFieldset($form, $integrationData)
    {
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('General')]);

        $disabled = false;
        if (isset($integrationData[self::DATA_ID])) {
            $fieldset->addField(self::DATA_ID, 'hidden', ['name' => 'id']);

            if ($integrationData[self::DATA_SETUP_TYPE] == IntegrationModel::TYPE_CONFIG) {
                $disabled = true;
            }
        }

        $fieldset->addField(
            self::DATA_NAME,
            'text',
            [
                'label' => __('Name'),
                'name' => self::DATA_NAME,
                'required' => true,
                'disabled' => $disabled,
                'maxlength' => '255'
            ]
        );
        $fieldset->addField(
            self::DATA_EMAIL,
            'text',
            [
                'label' => __('Email'),
                'name' => self::DATA_EMAIL,
                'disabled' => $disabled,
                'class' => 'validate-email',
                'maxlength' => '254'
            ]
        );
        $fieldset->addField(
            self::DATA_ENDPOINT,
            'text',
            [
                'label' => __('Callback URL'),
                'name' => self::DATA_ENDPOINT,
                'disabled' => $disabled,
                'class' => 'validate-url',
                // @codingStandardsIgnoreStart
                'note' => __(
                    'Enter URL where Oauth credentials can be sent when using Oauth for token exchange. We strongly recommend using https://.'
                )
                // @codingStandardsIgnoreEnd
            ]
        );
        $fieldset->addField(
            self::DATA_IDENTITY_LINK_URL,
            'text',
            [
                'label' => __('Identity link URL'),
                'name' => self::DATA_IDENTITY_LINK_URL,
                'disabled' => $disabled,
                'class' => 'validate-url',
                'note' => __(
                    'URL to redirect user to link their 3rd party account with this Magento integration credentials.'
                )
            ]
        );

        $currentUserVerificationFieldset = $form->addFieldset(
            'current_user_verification_fieldset',
            ['legend' => __('Current User Identity Verification')]
        );
        $currentUserVerificationFieldset->addField(
            self::DATA_CONSUMER_PASSWORD,
            'password',
            [
                'name' => self::DATA_CONSUMER_PASSWORD,
                'label' => __('Your Password'),
                'id' => self::DATA_CONSUMER_PASSWORD,
                'title' => __('Your Password'),
                'class' => 'validate-current-password required-entry',
                'required' => true
            ]
        );
    }

    /**
     * Add fieldset with integration details. This fieldset is available for existing integrations only.
     *
     * @param \Magento\Framework\Data\Form $form
     * @param array $integrationData
     * @return void
     */
    protected function _addDetailsFieldset($form, $integrationData)
    {
        if (isset($integrationData[self::DATA_ID])) {
            $fieldset = $form->addFieldset('details_fieldset', ['legend' => __('Integration Details')]);
            /** @var \Magento\Integration\Block\Adminhtml\Integration\Tokens $tokensBlock */
            $tokensBlock = $this->getChildBlock('integration_tokens');
            foreach ($tokensBlock->getFormFields() as $field) {
                $fieldset->addField($field['name'], $field['type'], $field['metadata']);
            }
        }
    }
}
