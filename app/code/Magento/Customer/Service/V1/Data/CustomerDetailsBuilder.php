<?php
/**
 * CustomerDetailsBuilder class
 *
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
namespace Magento\Customer\Service\V1\Data;

use Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder;
use Magento\Framework\Service\Data\AttributeValueBuilder;
use Magento\Framework\Service\Data\MetadataServiceInterface;

/**
 * Class CustomerDetailsBuilder
 */
class CustomerDetailsBuilder extends AbstractExtensibleObjectBuilder
{
    /**
     * Customer builder
     *
     * @var \Magento\Customer\Service\V1\Data\CustomerBuilder
     */
    protected $_customerBuilder;

    /**
     * Address builder
     *
     * @var \Magento\Customer\Service\V1\Data\AddressBuilder
     */
    protected $_addressBuilder;

    /**
     * @param \Magento\Framework\Service\Data\ObjectFactory $objectFactory
     * @param AttributeValueBuilder $valueBuilder
     * @param MetadataServiceInterface $metadataService
     * @param CustomerBuilder $customerBuilder
     * @param AddressBuilder $addressBuilder
     */
    public function __construct(
        \Magento\Framework\Service\Data\ObjectFactory $objectFactory,
        AttributeValueBuilder $valueBuilder,
        MetadataServiceInterface $metadataService,
        \Magento\Customer\Service\V1\Data\CustomerBuilder $customerBuilder,
        \Magento\Customer\Service\V1\Data\AddressBuilder $addressBuilder
    ) {
        parent::__construct($objectFactory, $valueBuilder, $metadataService);
        $this->_customerBuilder = $customerBuilder;
        $this->_addressBuilder = $addressBuilder;
    }

    /**
     * {@inheritdoc}
     */
    protected function _setDataValues(array $data)
    {
        $newData = array();
        if (isset($data[CustomerDetails::KEY_CUSTOMER])) {
            $newData[CustomerDetails::KEY_CUSTOMER] = $this->_customerBuilder->populateWithArray(
                $data[CustomerDetails::KEY_CUSTOMER]
            )->create();
        }

        if (isset($data[CustomerDetails::KEY_ADDRESSES])) {
            $newData[CustomerDetails::KEY_ADDRESSES] = array();
            $addresses = $data[CustomerDetails::KEY_ADDRESSES];
            foreach ($addresses as $address) {
                $newData[CustomerDetails::KEY_ADDRESSES][] = $this->_addressBuilder->populateWithArray(
                    $address
                )->create();
            }
        }

        return parent::_setDataValues($newData);
    }

    /**
     * Set customer
     *
     * @param \Magento\Customer\Service\V1\Data\Customer $customer
     * @return $this
     */
    public function setCustomer(Customer $customer)
    {
        return $this->_set(CustomerDetails::KEY_CUSTOMER, $customer);
    }

    /**
     * Set addresses
     *
     * @param \Magento\Customer\Service\V1\Data\Address[]|null $addresses
     * @return $this
     */
    public function setAddresses($addresses)
    {
        return $this->_set(CustomerDetails::KEY_ADDRESSES, $addresses);
    }

    /**
     * Builds the entity.
     *
     * @return \Magento\Customer\Service\V1\Data\CustomerDetails
     */
    public function create()
    {
        if (!isset($this->_data[CustomerDetails::KEY_CUSTOMER])) {
            $this->_data[CustomerDetails::KEY_CUSTOMER] = $this->_customerBuilder->create();
        }
        if (!isset($this->_data[CustomerDetails::KEY_ADDRESSES])) {
            $this->_data[CustomerDetails::KEY_ADDRESSES] = null;
        }
        return parent::create();
    }
}
