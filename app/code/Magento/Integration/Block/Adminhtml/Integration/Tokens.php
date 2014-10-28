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
            array('legend' => __('Integration Tokens for Extensions'), 'class' => 'fieldset-wide')
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
        return array(
            array(
                'name' => self::DATA_CONSUMER_KEY,
                'type' => 'text',
                'metadata' => array(
                    'label' => __('Consumer Key'),
                    'name' => self::DATA_CONSUMER_KEY,
                    'readonly' => true
                )
            ),
            array(
                'name' => self::DATA_CONSUMER_SECRET,
                'type' => 'text',
                'metadata' => array(
                    'label' => __('Consumer Secret'),
                    'name' => self::DATA_CONSUMER_SECRET,
                    'readonly' => true
                )
            ),
            array(
                'name' => self::DATA_TOKEN,
                'type' => 'text',
                'metadata' => array('label' => __('Access Token'), 'name' => self::DATA_TOKEN, 'readonly' => true)
            ),
            array(
                'name' => self::DATA_TOKEN_SECRET,
                'type' => 'text',
                'metadata' => array(
                    'label' => __('Access Token Secret'),
                    'name' => self::DATA_TOKEN_SECRET,
                    'readonly' => true
                )
            )
        );
    }
}
