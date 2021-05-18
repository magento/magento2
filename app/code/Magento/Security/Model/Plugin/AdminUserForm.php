<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Security\Model\Plugin;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Security\Model\ResourceModel\UserExpiration;
use Magento\Security\Model\UserExpirationFactory;
use Magento\User\Block\User\Edit\Tab\Main;

/**
 * Add the `expires_at` form field to the User main form.
 */
class AdminUserForm
{
    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    /**
     * @var UserExpiration
     */
    private $userExpirationResource;

    /**
     * @var UserExpirationFactory
     */
    private $userExpirationFactory;

    /**
     * @param TimezoneInterface $localeDate
     * @param UserExpirationFactory $userExpirationFactory
     * @param UserExpiration $userExpirationResource
     */
    public function __construct(
        TimezoneInterface $localeDate,
        UserExpirationFactory $userExpirationFactory,
        UserExpiration $userExpirationResource
    ) {
        $this->localeDate = $localeDate;
        $this->userExpirationResource = $userExpirationResource;
        $this->userExpirationFactory = $userExpirationFactory;
    }

    /**
     * Add the `expires_at` field to the admin user edit form.
     *
     * @param Main $subject
     * @param \Closure $proceed
     * @return mixed
     */
    public function aroundGetFormHtml(
        Main $subject,
        \Closure $proceed
    ): string {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $subject->getForm();
        if (is_object($form)) {
            $dateFormat = $this->localeDate->getDateFormat(\IntlDateFormatter::SHORT);
            $timeFormat = $this->localeDate->getTimeFormat(\IntlDateFormatter::SHORT);
            $fieldset = $form->getElement('base_fieldset');
            $userIdField = $fieldset->getElements()->searchById('user_id');
            $userExpirationValue = null;
            if ($userIdField) {
                $userId = (int)$userIdField->getValue();
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
     * @param int $userId
     * @return string
     */
    private function loadUserExpirationByUserId(int $userId): string
    {
        /** @var \Magento\Security\Model\UserExpiration $userExpiration */
        $userExpiration = $this->userExpirationFactory->create();
        $this->userExpirationResource->load($userExpiration, $userId);

        return $userExpiration->getExpiresAt();
    }
}
