<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Plugin\Block\Adminhtml\Integration\Edit\Tab;

use Magento\AdminAdobeIms\Plugin\AdobeImsReauth\AddAdobeImsReAuthButton;
use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\Integration\Block\Adminhtml\Integration\Edit\Tab\Info;

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
     * Add adobeIms reAuth button to integration new/edit form
     *
     * @param Info $subject
     * @return void
     */
    public function beforeGetFormHtml(Info $subject): void
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
