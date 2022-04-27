<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Plugin\Block\Adminhtml\User\Edit\Tab;

use Closure;
use Magento\Framework\Exception\LocalizedException;
use Magento\User\Block\User\Edit\Tab\Main;

class UserField
{
    /**
     * @param Main $subject
     * @param Closure $proceed
     * @return mixed
     * @throws LocalizedException
     */
    public function aroundGetFormHtml(Main $subject, Closure $proceed)
    {
        $form = $subject->getForm();
        if (is_object($form)) {
            $verificationFieldset = $form->getElement('current_user_verification_fieldset');
            if ($verificationFieldset !== null) {
                $verificationFieldset->addField(
                    'ims_verification',
                    'button',
                    [
                        'name' => 'ims_verification',
                        'label' => __('Verify Identity with Adobe IMS'),
                        'id' => 'ims_verification',
                        'title' => __('Verify Identity with Adobe IMS'),
                        'required' => true,
                        'value' => __('Sign In with Adobe IMS'),
                    ]
                );

                $verificationFieldset->addField(
                    'verified',
                    'hidden',
                    [
                        'name' => 'verified',
                        'label' => __('Verify Identity with Adobe IMS'),
                        'id' => 'verified',
                        'title' => __('Verify Identity with Adobe IMS'),
                        'required' => true,
                    ]
                );

                $subject->setForm($form);
            }
        }

        return $proceed();
    }
}
