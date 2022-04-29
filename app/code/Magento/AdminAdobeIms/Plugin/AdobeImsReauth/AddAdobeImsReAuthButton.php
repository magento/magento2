<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Plugin\AdobeImsReauth;

use Magento\Framework\Data\Form\Element\AbstractElement;

class AddAdobeImsReAuthButton
{
    /**
     * Add AdobeIMS ReAuth Button
     *
     * @param AbstractElement $fieldset
     * @return void
     */
    public function addAdobeImsReAuthButton(AbstractElement $fieldset): void
    {
        $fieldset->addField(
            'ims_verification',
            'button',
            [
                'name' => 'ims_verification',
                'label' => __('Verify Identity with Adobe IMS'),
                'id' => 'ims_verification',
                'class' => 'ims_verification',
                'title' => __('Verify Identity with Adobe IMS'),
                'required' => true,
                'value' => __('Sign In with Adobe IMS'),
            ]
        );

        $fieldset->addField(
            'ims_verified',
            'hidden',
            [
                'name' => 'ims_verified',
                'label' => __('Verify Identity with Adobe IMS'),
                'id' => 'ims_verified',
                'class' => 'ims_verified',
                'title' => __('Verify Identity with Adobe IMS'),
                'required' => true,
            ]
        );
    }
}
