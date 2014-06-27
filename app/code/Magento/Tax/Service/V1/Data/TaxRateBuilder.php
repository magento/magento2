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
namespace Magento\Tax\Service\V1\Data;

use Magento\Framework\Service\Data\ObjectFactory;

/**
 * Builder for the TaxRate Service Data Object
 *
 * @method TaxRate create()
 */
class TaxRateBuilder extends \Magento\Framework\Service\Data\AbstractObjectBuilder
{
    /**
     * ZipRange builder
     *
     * @var ZipRangeBuilder
     */
    protected $zipRangeBuilder;

    /**
     * Initialize dependencies.
     *
     * @param ObjectFactory $objectFactory
     * @param ZipRangeBuilder $zipRangeBuilder
     */
    public function __construct(
        ObjectFactory $objectFactory,
        ZipRangeBuilder $zipRangeBuilder
    ) {
        parent::__construct($objectFactory);
        $this->zipRangeBuilder = $zipRangeBuilder;
    }

    /**
     * Set id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        $this->_set(TaxRate::KEY_ID, $id);
        return $this;
    }

    /**
     * Set country id
     *
     * @param string $countryId
     * @return $this
     */
    public function setCountryId($countryId)
    {
        $this->_set(TaxRate::KEY_COUNTRY_ID, $countryId);
        return $this;
    }

    /**
     * Set region id
     *
     * @param int $regionId
     * @return $this
     */
    public function setRegionId($regionId)
    {
        $this->_set(TaxRate::KEY_REGION_ID, $regionId);
        return $this;
    }

    /**
     * Set postcode
     *
     * @param string $postcode
     * @return $this
     */
    public function setPostcode($postcode)
    {
        $this->_set(TaxRate::KEY_POSTCODE, $postcode);
        return $this;
    }

    /**
     * Set zip range
     *
     * @param \Magento\Tax\Service\V1\Data\ZipRange $zipRange
     * @return $this
     */
    public function setZipRange($zipRange)
    {
        $this->_set(TaxRate::KEY_ZIP_RANGE, $zipRange);
        return $this;
    }

    /**
     * Set tax rate in percentage
     *
     * @param float $rate
     * @return $this
     */
    public function setPercentageRate($rate)
    {
        $this->_set(TaxRate::KEY_PERCENTAGE_RATE, $rate);
        return $this;
    }

    /**
     * Set tax rate code
     *
     * @param string $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->_set(TaxRate::KEY_CODE, $code);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function _setDataValues(array $data)
    {
        if (array_key_exists(TaxRate::KEY_ZIP_RANGE, $data)) {
            $data[TaxRate::KEY_ZIP_RANGE] =
                $this->zipRangeBuilder->populateWithArray($data[TaxRate::KEY_ZIP_RANGE])->create();
        }
        return parent::_setDataValues($data);
    }
}
