<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Security\Model\Plugin;

/**
 * Add the `expires_at` form field to the User main form.
 *
 * @package Magento\Security\Model\Plugin
 */
class AdminUserForm
{

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $localeDate;

    /**
     * UserForm constructor.
     *
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     */
    public function __construct(\Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate)
    {
        $this->localeDate = $localeDate;
    }

    /**
     * Add the `expires_at` field to the admin user edit form.
     * @param \Magento\User\Block\User\Edit\Tab\Main $subject
     * @param \Closure $proceed
     * @return mixed
     */
    public function aroundGetFormHtml(
        \Magento\User\Block\User\Edit\Tab\Main $subject,
        \Closure $proceed
    ) {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $subject->getForm();
        if (is_object($form)) {
            $dateFormat = $this->localeDate->getDateFormat(
                \IntlDateFormatter::MEDIUM
            );
            $timeFormat = $this->localeDate->getTimeFormat(
                \IntlDateFormatter::MEDIUM
            );
            $fieldset = $form->getElement('base_fieldset');
            $fieldset->addField(
                'expires_at',
                'date',
                [
                    'name' => 'expires_at',
                    'label' => __('Expiration Date'),
                    'title' => __('Expiration Date'),
                    'date_format' => $dateFormat,
                    'time_format' => $timeFormat,
                    'class' => 'validate-date',
                ]
            );

            $subject->setForm($form);
        }

        return $proceed();
    }
}
