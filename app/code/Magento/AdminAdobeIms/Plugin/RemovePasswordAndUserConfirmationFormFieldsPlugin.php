<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Plugin;

use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\Backend\Block\Widget\Form as WidgetForm;
use Magento\Framework\Data\Form as DataForm;

class RemovePasswordAndUserConfirmationFormFieldsPlugin
{
    /**
     * @var ImsConfig
     */
    private ImsConfig $adminImsConfig;

    /**
     * @param ImsConfig $adminImsConfig
     */
    public function __construct(ImsConfig $adminImsConfig)
    {
        $this->adminImsConfig = $adminImsConfig;
    }

    /**
     * Remove user password and confirmation field and hide the user verification fieldset
     *
     * @param WidgetForm $subject
     * @param DataForm $result
     * @return DataForm
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetForm(WidgetForm $subject, DataForm $result): DataForm
    {
        if ($this->adminImsConfig->enabled() === false) {
            return $result;
        }

        if ($result->getElement('base_fieldset')) {
            foreach ($result->getElement('base_fieldset')->getElements() as $element) {
                if ($element->getId() === 'email') {
                    $element->setData('note', __('Use the same email user has in Adobe IMS organization.'));
                }
                if ($element->getId() === 'password') {
                    $result->getElement('base_fieldset')->removeField($element->getId());
                }

                if ($element->getId() === 'confirmation') {
                    $result->getElement('base_fieldset')->removeField($element->getId());
                }
            }
        }

        if ($result->getElement('current_user_verification_fieldset')) {
            foreach ($result->getElement('current_user_verification_fieldset')->getElements() as $element) {
                if ($element->getId() === 'current_password') {
                    $element->setType('hidden');
                    $element->setClass('');

                    /**
                     * We can set the value to "randomPassword", because it must just pass the input validation rules
                     *  we also don't use this value anymore and also don't save this anywhere
                     *  because we are using the access_token for the verification and not the current user password
                     */
                    $element->setData('value', 'randomPassword');
                }
            }
        }

        return $result;
    }
}
