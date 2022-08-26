<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminAdobeIms\Plugin\Block\Adminhtml\System\Account\Edit;

use Magento\AdminAdobeIms\Plugin\AdobeImsReauth\AddAdobeImsReAuthButton;
use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\Backend\Block\System\Account\Edit\Form;

class AddReAuthVerification
{
    /**
     * @var AddAdobeImsReAuthButton
     */
    private AddAdobeImsReAuthButton $adobeImsReAuthButton;

    /**
     * @var ImsConfig
     */
    private ImsConfig $adminAdobeImsConfig;

    /**
     * @param AddAdobeImsReAuthButton $adobeImsReAuthButton
     * @param ImsConfig $adminAdobeImsConfig
     */
    public function __construct(
        AddAdobeImsReAuthButton $adobeImsReAuthButton,
        ImsConfig $adminAdobeImsConfig
    ) {
        $this->adobeImsReAuthButton = $adobeImsReAuthButton;
        $this->adminAdobeImsConfig = $adminAdobeImsConfig;
    }

    /**
     * Add adobeIms reAuth button to account edit form
     *
     * @param Form $subject
     * @return void
     */
    public function beforeGetFormHtml(Form $subject): void
    {
        if ($this->adminAdobeImsConfig->enabled()) {
            $form = $subject->getForm();
            if (is_object($form)) {
                $verificationFieldset = $form->getElement('current_user_verification_fieldset');
                if ($verificationFieldset !== null) {
                    $this->adobeImsReAuthButton->addAdobeImsReAuthButton($verificationFieldset);
                    $subject->setForm($form);
                }
            }
        }
    }
}
