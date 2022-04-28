<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Plugin\Block\Adminhtml\System\Account\Edit;

use Closure;
use Magento\AdminAdobeIms\Plugin\AdobeImsReauth\AddAdobeImsReAuthButton;
use Magento\Backend\Block\System\Account\Edit\Form;

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
     * @param Form $subject
     * @param Closure $proceed
     * @return mixed
     */
    public function aroundGetFormHtml(Form $subject, Closure $proceed)
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
