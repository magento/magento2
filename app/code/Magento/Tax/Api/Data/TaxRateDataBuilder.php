<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Api\Data;

/**
 * DataBuilder class for \Magento\Tax\Api\Data\TaxRateInterface
 * @codeCoverageIgnore
 */
class TaxRateDataBuilder extends \Magento\Framework\Api\Builder
{
    /**
     * @param \Magento\Framework\Api\ObjectFactory $objectFactory
     * @param \Magento\Framework\Api\MetadataServiceInterface $metadataService
     * @param \Magento\Framework\Api\AttributeDataBuilder $attributeValueBuilder
     * @param \Magento\Framework\Reflection\DataObjectProcessor $objectProcessor
     * @param \Magento\Framework\Reflection\TypeProcessor $typeProcessor
     * @param \Magento\Framework\Serialization\DataBuilderFactory $dataBuilderFactory
     * @param \Magento\Framework\ObjectManager\ConfigInterface $objectManagerConfig
     * @param string|null $modelClassInterface
     */
    public function __construct(
        \Magento\Framework\Api\ObjectFactory $objectFactory,
        \Magento\Framework\Api\MetadataServiceInterface $metadataService,
        \Magento\Framework\Api\AttributeDataBuilder $attributeValueBuilder,
        \Magento\Framework\Reflection\DataObjectProcessor $objectProcessor,
        \Magento\Framework\Reflection\TypeProcessor $typeProcessor,
        \Magento\Framework\Serialization\DataBuilderFactory $dataBuilderFactory,
        \Magento\Framework\ObjectManager\ConfigInterface $objectManagerConfig,
        $modelClassInterface = null
    ) {
        parent::__construct(
            $objectFactory,
            $metadataService,
            $attributeValueBuilder,
            $objectProcessor,
            $typeProcessor,
            $dataBuilderFactory,
            $objectManagerConfig,
            'Magento\Tax\Api\Data\TaxRateInterface'
        );
    }

    /**
     * @param int|null $id
     * @return $this
     */
    public function setId($id)
    {
        $this->_set('id', $id);
        return $this;
    }

    /**
     * @param string $taxCountryId
     * @return $this
     */
    public function setTaxCountryId($taxCountryId)
    {
        $this->_set('tax_country_id', $taxCountryId);
        return $this;
    }

    /**
     * @param int|null $taxRegionId
     * @return $this
     */
    public function setTaxRegionId($taxRegionId)
    {
        $this->_set('tax_region_id', $taxRegionId);
        return $this;
    }

    /**
     * @param string|null $regionName
     * @return $this
     */
    public function setRegionName($regionName)
    {
        $this->_set('region_name', $regionName);
        return $this;
    }

    /**
     * @param string|null $taxPostcode
     * @return $this
     */
    public function setTaxPostcode($taxPostcode)
    {
        $this->_set('tax_postcode', $taxPostcode);
        return $this;
    }

    /**
     * @param int|null $zipFrom
     * @return $this
     */
    public function setZipFrom($zipFrom)
    {
        $this->_set('zip_from', $zipFrom);
        return $this;
    }

    /**
     * @param int|null $zipTo
     * @return $this
     */
    public function setZipTo($zipTo)
    {
        $this->_set('zip_to', $zipTo);
        return $this;
    }

    /**
     * @param float $rate
     * @return $this
     */
    public function setRate($rate)
    {
        $this->_set('rate', $rate);
        return $this;
    }

    /**
     * @param string $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->_set('code', $code);
        return $this;
    }

    /**
     * @param \Magento\Tax\Api\Data\TaxRateTitleInterface $titles
     * @return $this
     */
    public function setTitles($titles)
    {
        $this->_set('titles', $titles);
        return $this;
    }

    /**
     * @param int|null $zipIsRange
     * @return $this
     */
    public function setZipIsRange($zipIsRange)
    {
        $this->_set('zip_is_range', $zipIsRange);
        return $this;
    }
    /**
     * {@inheritdoc}
     */
    public function create()
    {
        /** TODO: temporary fix while problem with hasDataChanges flag not solved. MAGETWO-30324 */
        $object = parent::create();
        $object->setDataChanges(true);
        return $object;
    }
}
