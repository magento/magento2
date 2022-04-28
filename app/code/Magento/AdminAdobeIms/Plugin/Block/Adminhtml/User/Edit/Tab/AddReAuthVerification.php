<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Plugin\Block\Adminhtml\User\Edit\Tab;

use Closure;
use Magento\AdminAdobeIms\Plugin\AdobeImsReauth\AddAdobeImsReAuthButton;
use Magento\User\Block\User\Edit\Tab\Main;

class AddReAuthVerification
{
    /**
     * @var AddAdobeImsReAuthButton
     */
    private AddAdobeImsReAuthButton $adobeImsReAuthButton;

    /**
     * @param AddAdobeImsReAuthButton $adobeImsReAuthButton
     */
    public function __construct(
        AddAdobeImsReAuthButton $adobeImsReAuthButton
    ) {
        $this->adobeImsReAuthButton = $adobeImsReAuthButton;
    }

    /**
     * Add adobeIms reAuth button to user edit and create form
     *
     * @param Main $subject
     * @param Closure $proceed
     * @return mixed
     */
    public function aroundGetFormHtml(Main $subject, Closure $proceed)
    {
        $form = $subject->getForm();
        if (is_object($form)) {
            $verificationFieldset = $form->getElement('current_user_verification_fieldset');
            if ($verificationFieldset !== null) {
                $this->adobeImsReAuthButton->addAdobeImsReAuthButton($verificationFieldset);
                $subject->setForm($form);
            }
        }

        return $proceed();
    }
}
