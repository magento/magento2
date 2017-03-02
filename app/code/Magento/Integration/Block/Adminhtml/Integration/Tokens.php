<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Block\Adminhtml\Integration;

use Magento\Integration\Controller\Adminhtml\Integration as IntegrationController;

/**
 * Main Integration properties edit form
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Tokens extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**#@+
     * Form element name.
     */
    const DATA_TOKEN = 'token';
    const DATA_TOKEN_SECRET = 'token_secret';
    const DATA_CONSUMER_KEY = 'consumer_key';
    const DATA_CONSUMER_SECRET = 'consumer_secret';
    /**#@-*/

    /**
     * Set form id prefix, declare fields for integration consumer modal
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $htmlIdPrefix = 'integration_token_';
        $form->setHtmlIdPrefix($htmlIdPrefix);

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Integration Tokens for Extensions'), 'class' => ' fieldset-wide']
        );

        foreach ($this->getFormFields() as $field) {
            $fieldset->addField($field['name'], $field['type'], $field['metadata']);
        }

        $integrationData = $this->_coreRegistry->registry(IntegrationController::REGISTRY_KEY_CURRENT_INTEGRATION);
        if ($integrationData) {
            $form->setValues($integrationData);
        }
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Return a list of form fields with oAuth credentials.
     *
     * @return array
     */
    public function getFormFields()
    {
        return [
            [
                'name' => self::DATA_CONSUMER_KEY,
                'type' => 'text',
                'metadata' => [
                    'label' => __('Consumer Key'),
                    'name' => self::DATA_CONSUMER_KEY,
                    'readonly' => true,
                ],
            ],
            [
                'name' => self::DATA_CONSUMER_SECRET,
                'type' => 'text',
                'metadata' => [
                    'label' => __('Consumer Secret'),
                    'name' => self::DATA_CONSUMER_SECRET,
                    'readonly' => true,
                ]
            ],
            [
                'name' => self::DATA_TOKEN,
                'type' => 'text',
                'metadata' => ['label' => __('Access Token'), 'name' => self::DATA_TOKEN, 'readonly' => true]
            ],
            [
                'name' => self::DATA_TOKEN_SECRET,
                'type' => 'text',
                'metadata' => [
                    'label' => __('Access Token Secret'),
                    'name' => self::DATA_TOKEN_SECRET,
                    'readonly' => true,
                ]
            ]
        ];
    }
}
