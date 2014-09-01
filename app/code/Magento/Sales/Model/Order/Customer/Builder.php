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

namespace Magento\Sales\Model\Order\Customer;

/**
 * Class Builder
 */
class Builder
{
    /**
     * @var \Magento\Framework\ObjectManager
     */
    protected $objectManager;

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
     * @var int
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
     * @var int
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
     * @param \Magento\Framework\ObjectManager $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManager $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * @param string $customerDob
     * @return \Magento\Sales\Model\Order\Customer\Builder
     */
    public function setDob($customerDob)
    {
        $this->customerDob = $customerDob;
        return $this;
    }

    /**
     * @param string $customerEmail
     * @return \Magento\Sales\Model\Order\Customer\Builder
     */
    public function setEmail($customerEmail)
    {
        $this->customerEmail = $customerEmail;
        return $this;
    }

    /**
     * @param string $customerFirstName
     * @return \Magento\Sales\Model\Order\Customer\Builder
     */
    public function setFirstName($customerFirstName)
    {
        $this->customerFirstName = $customerFirstName;
        return $this;
    }

    /**
     * @param string $customerGender
     * @return \Magento\Sales\Model\Order\Customer\Builder
     */
    public function setGender($customerGender)
    {
        $this->customerGender = $customerGender;
        return $this;
    }

    /**
     * @param int $customerGroupId
     * @return \Magento\Sales\Model\Order\Customer\Builder
     */
    public function setGroupId($customerGroupId)
    {
        $this->customerGroupId = $customerGroupId;
        return $this;
    }

    /**
     * @param int $customerId
     * @return \Magento\Sales\Model\Order\Customer\Builder
     */
    public function setId($customerId)
    {
        $this->customerId = $customerId;
        return $this;
    }

    /**
     * @param int $customerIsGuest
     * @return \Magento\Sales\Model\Order\Customer\Builder
     */
    public function setIsGuest($customerIsGuest)
    {
        $this->customerIsGuest = $customerIsGuest;
        return $this;
    }

    /**
     * @param string $customerLastName
     * @return \Magento\Sales\Model\Order\Customer\Builder
     */
    public function setLastName($customerLastName)
    {
        $this->customerLastName = $customerLastName;
        return $this;
    }

    /**
     * @param string $customerMiddleName
     * @return \Magento\Sales\Model\Order\Customer\Builder
     */
    public function setMiddleName($customerMiddleName)
    {
        $this->customerMiddleName = $customerMiddleName;
        return $this;
    }

    /**
     * @param string $customerNote
     * @return \Magento\Sales\Model\Order\Customer\Builder
     */
    public function setNote($customerNote)
    {
        $this->customerNote = $customerNote;
        return $this;
    }

    /**
     * @param int $customerNoteNotify
     * @return \Magento\Sales\Model\Order\Customer\Builder
     */
    public function setNoteNotify($customerNoteNotify)
    {
        $this->customerNoteNotify = $customerNoteNotify;
        return $this;
    }

    /**
     * @param string $customerPrefix
     * @return \Magento\Sales\Model\Order\Customer\Builder
     */
    public function setPrefix($customerPrefix)
    {
        $this->customerPrefix = $customerPrefix;
        return $this;
    }

    /**
     * @param string $customerSuffix
     * @return \Magento\Sales\Model\Order\Customer\Builder
     */
    public function setSuffix($customerSuffix)
    {
        $this->customerSuffix = $customerSuffix;
        return $this;
    }

    /**
     * @param string $customerTaxvat
     * @return \Magento\Sales\Model\Order\Customer\Builder
     */
    public function setTaxvat($customerTaxvat)
    {
        $this->customerTaxvat = $customerTaxvat;
        return $this;
    }

    /**
     * @return \Magento\Sales\Model\Order\Customer
     */
    public function create()
    {
        return $this->objectManager->create('Magento\Sales\Model\Order\Customer', [
            'customerDob' => $this->customerDob,
            'customerEmail' => $this->customerEmail,
            'customerFirstName' => $this->customerFirstName,
            'customerGender' => $this->customerGender,
            'customerGroupId' => $this->customerGroupId,
            'customerId' => $this->customerId,
            'customerIsGuest' => $this->customerIsGuest,
            'customerLastName' => $this->customerLastName,
            'customerMiddleName' => $this->customerMiddleName,
            'customerNote' => $this->customerNote,
            'customerNoteNotify' => $this->customerNoteNotify,
            'customerPrefix' => $this->customerPrefix,
            'customerSuffix' => $this->customerSuffix,
            'customerTaxvat' => $this->customerTaxvat
        ]);
    }
}
