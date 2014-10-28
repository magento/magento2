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

 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Customer\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Customer\Model\AddressFactory;

/**
 * Registry for Address models
 */
class AddressRegistry
{
    /**
     * @var Address[]
     */
    protected $registry = [];

    /**
     * @var AddressFactory
     */
    protected $addressFactory;

    /**
     * @param AddressFactory $addressFactory
     */
    public function __construct(AddressFactory $addressFactory)
    {
        $this->addressFactory = $addressFactory;
    }

    /**
     * Get instance of the Address Model identified by id
     *
     * @param int $addressId
     * @return Address
     * @throws NoSuchEntityException
     */
    public function retrieve($addressId)
    {
        if (isset($this->registry[$addressId])) {
            return $this->registry[$addressId];
        }
        $address = $this->addressFactory->create();
        $address->load($addressId);
        if (!$address->getId()) {
            throw NoSuchEntityException::singleField('addressId', $addressId);
        }
        $this->registry[$addressId] = $address;
        return $address;
    }

    /**
     * Remove an instance of the Address Model from the registry
     *
     * @param int $addressId
     * @return void
     */
    public function remove($addressId)
    {
        unset($this->registry[$addressId]);
    }
}
