<?php
/**
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
namespace Magento\Wonderland\Model;

use Magento\Wonderland\Api\Data\FakeAddressInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

class FakeAddress extends AbstractExtensibleModel implements FakeAddressInterface
{
    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * Get customer ID
     *
     * @return int|null
     */
    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * Get region
     *
     * @return \Magento\Wonderland\Api\Data\FakeRegionInterface|null
     */
    public function getRegion()
    {
        return $this->getData(self::REGION);
    }

    /**
     * Get region
     *
     * @return \Magento\Wonderland\Api\Data\FakeRegionInterface|null
     */
    public function getRegions()
    {
        return $this->getData(self::REGIONS);
    }

    /**
     * Two-letter country code in ISO_3166-2 format
     *
     * @return string|null
     */
    public function getCountryId()
    {
        return $this->getData(self::COUNTRY_ID);
    }

    /**
     * Get street
     *
     * @return string[]|null
     */
    public function getStreet()
    {
        return $this->getData(self::STREET);
    }

    /**
     * Get company
     *
     * @return string|null
     */
    public function getCompany()
    {
        return $this->getData(self::COMPANY);
    }

    /**
     * Get telephone number
     *
     * @return string|null
     */
    public function getTelephone()
    {
        return $this->getData(self::TELEPHONE);
    }

    /**
     * Get fax number
     *
     * @return string|null
     */
    public function getFax()
    {
        return $this->getData(self::FAX);
    }

    /**
     * Get postcode
     *
     * @return string|null
     */
    public function getPostcode()
    {
        return $this->getData(self::POSTCODE);
    }

    /**
     * Get city name
     *
     * @return string|null
     */
    public function getCity()
    {
        return $this->getData(self::CITY);
    }

    /**
     * Get first name
     *
     * @return string|null
     */
    public function getFirstname()
    {
        return $this->getData(self::FIRSTNAME);
    }

    /**
     * Get last name
     *
     * @return string|null
     */
    public function getLastname()
    {
        return $this->getData(self::LASTNAME);
    }

    /**
     * Get middle name
     *
     * @return string|null
     */
    public function getMiddlename()
    {
        return $this->getData(self::MIDDLENAME);
    }

    /**
     * Get prefix
     *
     * @return string|null
     */
    public function getPrefix()
    {
        return $this->getData(self::PREFIX);
    }

    /**
     * Get suffix
     *
     * @return string|null
     */
    public function getSuffix()
    {
        return $this->getData(self::SUFFIX);
    }

    /**
     * Get Vat id
     *
     * @return string|null
     */
    public function getVatId()
    {
        return $this->getData(self::VAT_ID);
    }
}