<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Sales\Model\Order;


class Customer
{
    /**
     * @var string
     */
    protected $customerDob;

    /**
     * @var string
     */
    protected $customerEmail;

    /**
     * @var string
     */
    protected $customerFirstName;

    /**
     * @var string
     */
    protected $customerGender;

    /**
     * @var string
     */
    protected $customerGroupId;

    /**
     * @var int
     */
    protected $customerId;

    /**
     * @var int
     */
    protected $customerIsGuest;

    /**
     * @var string
     */
    protected $customerLastName;

    /**
     * @var string
     */
    protected $customerMiddleName;

    /**
     * @var string
     */
    protected $customerNote;

    /**
     * @var string
     */
    protected $customerNoteNotify;

    /**
     * @var string
     */
    protected $customerPrefix;

    /**
     * @var string
     */
    protected $customerSuffix;

    /**
     * @var string
     */
    protected $customerTaxvat;

    /**
     * @param string $customerDob
     * @param string $customerEmail
     * @param string $customerFirstName
     * @param string $customerGender
     * @param string $customerGroupId
     * @param int $customerId
     * @param int $customerIsGuest
     * @param string $customerLastName
     * @param string $customerMiddleName
     * @param string $customerNote
     * @param string $customerNoteNotify
     * @param string $customerPrefix
     * @param string $customerSuffix
     * @param string $customerTaxvat
     */
    public function __construct(
        $customerDob,
        $customerEmail,
        $customerFirstName,
        $customerGender,
        $customerGroupId,
        $customerId,
        $customerIsGuest,
        $customerLastName,
        $customerMiddleName,
        $customerNote,
        $customerNoteNotify,
        $customerPrefix,
        $customerSuffix,
        $customerTaxvat
    ) {
        $this->customerDob = $customerDob;
        $this->customerEmail = $customerEmail;
        $this->customerFirstName = $customerFirstName;
        $this->customerGender = $customerGender;
        $this->customerGroupId = $customerGroupId;
        $this->customerId = $customerId;
        $this->customerIsGuest = $customerIsGuest;
        $this->customerLastName = $customerLastName;
        $this->customerMiddleName = $customerMiddleName;
        $this->customerNote = $customerNote;
        $this->customerNoteNotify = $customerNoteNotify;
        $this->customerPrefix = $customerPrefix;
        $this->customerSuffix = $customerSuffix;
        $this->customerTaxvat = $customerTaxvat;
    }

    /**
     * @return string
     */
    public function getDob()
    {
        return $this->customerDob;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->customerEmail;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->customerFirstName;
    }

    /**
     * @return string
     */
    public function getGender()
    {
        return $this->customerGender;
    }

    /**
     * @return string
     */
    public function getGroupId()
    {
        return $this->customerGroupId;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->customerId;
    }

    /**
     * @return string
     */
    public function getIsGuest()
    {
        return $this->customerIsGuest;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->customerLastName;
    }

    /**
     * @return string
     */
    public function getMiddleName()
    {
        return $this->customerMiddleName;
    }

    /**
     * @return string
     */
    public function getNote()
    {
        return $this->customerNote;
    }

    /**
     * @return string
     */
    public function getNoteNotify()
    {
        return $this->customerNoteNotify;
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->customerPrefix;
    }

    /**
     * @return string
     */
    public function getSuffix()
    {
        return $this->customerSuffix;
    }

    /**
     * @return string
     */
    public function getTaxvat()
    {
        return $this->customerTaxvat;
    }
}
