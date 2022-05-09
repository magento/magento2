<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Plugin\Block\Adminhtml\User\Edit\Tab;

use Magento\AdminAdobeIms\Plugin\AdobeImsReauth\AddAdobeImsReAuthButton;
use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\User\Block\User\Edit\Tab\Main;

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
     * Add adobeIms reAuth button to user edit and create form
     *
     * @param Main $subject
     * @return void
     */
    public function beforeGetFormHtml(Main $subject): void
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
