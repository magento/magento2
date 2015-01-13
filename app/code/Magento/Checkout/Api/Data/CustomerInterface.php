<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Api\Data;

/**
 * @see \Magento\Checkout\Service\V1\Data\Cart\Customer
 * TODO: We can use \Magento\Customer\Api\Data\CustomerInterface for checkout flow. Need additional comments.
 */
interface CustomerInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Get customer id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get customer tax class id
     *
     * @return int|null
     */
    public function getTaxClassId();

    /**
     * Get customer group id
     *
     * @return int|null
     */
    public function getGroupId();

    /**
     * Get customer email
     *
     * @return string|null
     */
    public function getEmail();

    /**
     * Get customer name prefix
     *
     * @return string|null
     */
    public function getPrefix();

    /**
     * Get customer first name
     *
     * @return string|null
     */
    public function getFirstName();

    /**
     * Get customer middle name
     *
     * @return string|null
     */
    public function getMiddleName();

    /**
     * Get customer last name
     *
     * @return string|null
     */
    public function getLastName();

    /**
     * Get customer name suffix
     *
     * @return string|null
     */
    public function getSuffix();

    /**
     * Get customer date of birth
     *
     * @return string|null
     */
    public function getDob();

    /**
     * Get note
     *
     * @return string|null
     */
    public function getNote();

    /**
     * Get notification status
     *
     * @return string|null
     */
    public function getNoteNotify();

    /**
     * Is customer a guest?
     *
     * @return bool
     */
    public function getIsGuest();

    /**
     * Get  taxvat value
     *
     * @return string|null
     */
    public function getTaxVat();

    /**
     * Get gender
     *
     * @return string|null
     */
    public function getGender();
}
