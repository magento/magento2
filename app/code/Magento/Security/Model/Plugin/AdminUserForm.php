<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Security\Model\Plugin;

use Closure;
use IntlDateFormatter;
use Magento\Framework\Data\Form as FormData;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Security\Model\ResourceModel\UserExpiration;
use Magento\Security\Model\UserExpiration as ModelUserExpiration;
use Magento\Security\Model\UserExpirationFactory;
use Magento\User\Block\User\Edit\Tab\Main;

/**
 * Add the `expires_at` form field to the User main form.
 */
class AdminUserForm
{
    /**
     * UserForm constructor.
     *
     * @param TimezoneInterface $localeDate
     * @param UserExpirationFactory $userExpirationFactory
     * @param UserExpiration $userExpirationResource
     */
    public function __construct(
        private readonly TimezoneInterface $localeDate,
        private readonly UserExpirationFactory $userExpirationFactory,
        private readonly UserExpiration $userExpirationResource
    ) {
    }

    /**
     * Add the `expires_at` field to the admin user edit form.
     *
     * @param Main $subject
     * @param Closure $proceed
     * @return mixed
     */
    public function aroundGetFormHtml(
        Main $subject,
        Closure $proceed
    ) {
        /** @var FormData $form */
        $form = $subject->getForm();
        if (is_object($form)) {
            $dateFormat = $this->localeDate->getDateFormat(
                IntlDateFormatter::MEDIUM
            );
            $timeFormat = $this->localeDate->getTimeFormat(
                IntlDateFormatter::MEDIUM
            );
            $fieldset = $form->getElement('base_fieldset');
            $userIdField = $fieldset->getElements()->searchById('user_id');
            $userExpirationValue = null;
            if ($userIdField) {
                $userId = $userIdField->getValue();
                $userExpirationValue = $this->loadUserExpirationByUserId($userId);
            }
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
                    'value' => $userExpirationValue,
                ]
            );

            $subject->setForm($form);
        }

        return $proceed();
    }

    /**
     * Loads a user expiration record by user ID.
     *
     * @param string $userId
     * @return string
     */
    private function loadUserExpirationByUserId($userId)
    {
        /** @var ModelUserExpiration $userExpiration */
        $userExpiration = $this->userExpirationFactory->create();
        $this->userExpirationResource->load($userExpiration, $userId);
        return $userExpiration->getExpiresAt();
    }
}
