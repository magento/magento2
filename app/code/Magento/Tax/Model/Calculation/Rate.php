<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Tax\Model\Calculation;

use Magento\Directory\Model\Region;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Tax\Api\Data\TaxRateInterface;

/**
 * Tax Rate Model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Rate extends \Magento\Framework\Model\AbstractExtensibleModel implements TaxRateInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const KEY_ID              = 'id';
    const KEY_COUNTRY_ID      = 'tax_country_id';
    const KEY_REGION_ID       = 'tax_region_id';
    const KEY_REGION_NAME     = 'region_name';
    const KEY_POSTCODE        = 'tax_postcode';
    const KEY_ZIP_IS_RANGE    = 'zip_is_range';
    const KEY_ZIP_RANGE_FROM  = 'zip_from';
    const KEY_ZIP_RANGE_TO    = 'zip_to';
    const KEY_PERCENTAGE_RATE = 'rate';
    const KEY_CODE            = 'code';
    const KEY_TITLES          = 'titles';
    /**#@-*/

    /**#@-*/
    protected $_titles = null;

    /**
     * @var \Magento\Tax\Model\Calculation\Rate\Title
     */
    protected $_titleModel = null;

    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $_regionFactory;

    /**
     * @var \Magento\Tax\Model\Calculation\Rate\TitleFactory
     */
    protected $_titleFactory;
    /**
     * @var Region
     */
    protected $directoryRegion;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param Rate\TitleFactory $taxTitleFactory
     * @param Region $directoryRegion
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Tax\Model\Calculation\Rate\TitleFactory $taxTitleFactory,
        Region $directoryRegion,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_regionFactory = $regionFactory;
        $this->_titleFactory = $taxTitleFactory;
        $this->directoryRegion = $directoryRegion;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Magento model constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Tax\Model\ResourceModel\Calculation\Rate::class);
    }

    /**
     * Prepare location settings and tax postcode before save rate
     *
     * @return \Magento\Tax\Model\Calculation\Rate
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function beforeSave()
    {
        $isWrongRange = $this->getZipIsRange() && ($this->getZipFrom() === '' || $this->getZipTo() === '');

        $isEmptyValues = $this->getCode() === '' ||
            $this->getTaxCountryId() === '' ||
            $this->getRate() === '' ||
            ($this->getTaxPostcode() === '' && !$this->getZipIsRange());

        if ($isEmptyValues || $isWrongRange) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Make sure all required information is valid.')
            );
        }

        if (!is_numeric($this->getRate()) || $this->getRate() < 0) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The Rate Percent should be a positive number.')
            );
        }

        if ($this->getZipIsRange()) {
            $zipFrom = $this->getZipFrom();
            $zipTo = $this->getZipTo();

            if (strlen($zipFrom) > 9 || strlen($zipTo) > 9) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Maximum zip code length is 9.'));
            }

            if (!is_numeric($zipFrom) || !is_numeric($zipTo) || $zipFrom < 0 || $zipTo < 0) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Use digits only for the zip code.')
                );
            }

            if ($zipFrom > $zipTo) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Range To should be equal or greater than Range From.')
                );
            }

            $this->setTaxPostcode($zipFrom . '-' . $zipTo);
        } else {
            $taxPostCode = $this->getTaxPostcode();

            if (strlen($taxPostCode) > 10) {
                $taxPostCode = substr($taxPostCode, 0, 10);
            }

            $this->setTaxPostcode($taxPostCode)->setZipIsRange(null)->setZipFrom(null)->setZipTo(null);
        }

        parent::beforeSave();
        $country = $this->getTaxCountryId();
        $region = $this->getTaxRegionId();
        /** @var $regionModel \Magento\Directory\Model\Region */
        $regionModel = $this->_regionFactory->create();
        $regionModel->load($region);
        if ($regionModel->getCountryId() != $country) {
            $this->setTaxRegionId('*');
        }
        return $this;
    }

    /**
     * Save rate titles
     *
     * @return \Magento\Tax\Model\Calculation\Rate
     */
    public function afterSave()
    {
        $this->saveTitles();
        $this->_eventManager->dispatch('tax_settings_change_after');
        return parent::afterSave();
    }

    /**
     * Processing object before delete data
     *
     * @return \Magento\Tax\Model\Calculation\Rate
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeDelete()
    {
        if ($this->_isInRule()) {
            throw new CouldNotDeleteException(
                __('The tax rate cannot be removed. It exists in a tax rule.')
            );
        }
        return parent::beforeDelete();
    }

    /**
     * After rate delete
     * redeclared for dispatch tax_settings_change_after event
     *
     * @return \Magento\Tax\Model\Calculation\Rate
     */
    public function afterDelete()
    {
        $this->_eventManager->dispatch('tax_settings_change_after');
        return parent::afterDelete();
    }

    /**
     * Saves the tax titles
     *
     * @param array|null $titles
     * @return void
     */
    public function saveTitles($titles = null)
    {
        if (is_null($titles)) {
            $titles = $this->getTitle();
        }

        $this->getTitleModel()->deleteByRateId($this->getId());
        if (is_array($titles) && $titles) {
            foreach ($titles as $store => $title) {
                if ($title !== '') {
                    $this->getTitleModel()->setId(
                        null
                    )->setTaxCalculationRateId(
                        $this->getId()
                    )->setStoreId(
                        (int)$store
                    )->setValue(
                        $title
                    )->save();
                }
            }
        }
    }

    /**
     * Returns a tax title
     *
     * @return \Magento\Tax\Model\Calculation\Rate\Title
     */
    public function getTitleModel()
    {
        if (is_null($this->_titleModel)) {
            $this->_titleModel = $this->_titleFactory->create();
        }
        return $this->_titleModel;
    }

    /**
     * {@inheritdoc}
     */
    public function getTitles()
    {
        if ($this->getData(self::KEY_TITLES)) {
            return $this->getData(self::KEY_TITLES);
        }
        if (is_null($this->_titles)) {
            $this->_titles = $this->getTitleModel()->getCollection()->loadByRateId($this->getId())->getItems();
        }
        return $this->_titles;
    }

    /**
     * Deletes all tax rates
     *
     * @return \Magento\Tax\Model\Calculation\Rate
     */
    public function deleteAllRates()
    {
        $this->_getResource()->deleteAllRates();
        $this->_eventManager->dispatch('tax_settings_change_after');
        return $this;
    }

    /**
     * Load rate model by code
     *
     * @param  string $code
     * @return \Magento\Tax\Model\Calculation\Rate
     */
    public function loadByCode($code)
    {
        $this->load($code, 'code');
        return $this;
    }

    /**
     * Check if rate exists in tax rule
     *
     * @return array
     */
    protected function _isInRule()
    {
        return $this->getResource()->isInRule($this->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function getRegionName()
    {
        if (!$this->getData(self::KEY_REGION_NAME)) {
            $regionName = $this->directoryRegion->load($this->getTaxRegionId())->getCode();
            $this->setData(self::KEY_REGION_NAME, $regionName);
        }
        return $this->getData(self::KEY_REGION_NAME);
    }

    /**
     * @codeCoverageIgnoreStart
     * {@inheritdoc}
     */
    public function getTaxCalculationRateId()
    {
        return $this->getData(self::KEY_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getTaxCountryId()
    {
        return $this->getData(self::KEY_COUNTRY_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getTaxRegionId()
    {
        return $this->getData(self::KEY_REGION_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getTaxPostcode()
    {
        return $this->getData(self::KEY_POSTCODE);
    }

    /**
     * {@inheritdoc}
     */
    public function getZipFrom()
    {
        return $this->getData(self::KEY_ZIP_RANGE_FROM);
    }

    /**
     * {@inheritdoc}
     */
    public function getZipTo()
    {
        return $this->getData(self::KEY_ZIP_RANGE_TO);
    }

    /**
     * {@inheritdoc}
     */
    public function getRate()
    {
        return $this->getData(self::KEY_PERCENTAGE_RATE);
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->getData(self::KEY_CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function getZipIsRange()
    {
        return $this->getData(self::KEY_ZIP_IS_RANGE);
    }

    /**
     * Set country id
     *
     * @param string $taxCountryId
     * @return $this
     */
    public function setTaxCountryId($taxCountryId)
    {
        return $this->setData(self::KEY_COUNTRY_ID, $taxCountryId);
    }

    /**
     * Set region id
     *
     * @param int $taxRegionId
     * @return $this
     */
    public function setTaxRegionId($taxRegionId)
    {
        return $this->setData(self::KEY_REGION_ID, $taxRegionId);
    }

    /**
     * Set region name
     *
     * @param string $regionName
     * @return $this
     */
    public function setRegionName($regionName)
    {
        return $this->setData(self::KEY_REGION_NAME, $regionName);
    }

    /**
     * Set postcode
     *
     * @param string $taxPostCode
     * @return $this
     */
    public function setTaxPostcode($taxPostCode)
    {
        return $this->setData(self::KEY_POSTCODE, $taxPostCode);
    }

    /**
     * Set zip is range
     *
     * @param int $zipIsRange
     * @return $this
     */
    public function setZipIsRange($zipIsRange)
    {
        return $this->setData(self::KEY_ZIP_IS_RANGE, $zipIsRange);
    }

    /**
     * Set zip range from
     *
     * @param int $zipFrom
     * @return $this
     */
    public function setZipFrom($zipFrom)
    {
        return $this->setData(self::KEY_ZIP_RANGE_FROM, $zipFrom);
    }

    /**
     * Set zip range to
     *
     * @param int $zipTo
     * @return $this
     */
    public function setZipTo($zipTo)
    {
        return $this->setData(self::KEY_ZIP_RANGE_TO, $zipTo);
    }

    /**
     * Set tax rate in percentage
     *
     * @param float $rate
     * @return $this
     */
    public function setRate($rate)
    {
        return $this->setData(self::KEY_PERCENTAGE_RATE, $rate);
    }

    /**
     * Set tax rate code
     *
     * @param string $code
     * @return $this
     */
    public function setCode($code)
    {
        return $this->setData(self::KEY_CODE, $code);
    }

    /**
     * Set tax rate titles
     *
     * @param \Magento\Tax\Api\Data\TaxRateTitleInterface[] $titles
     * @return $this
     */
    public function setTitles(array $titles = null)
    {
        return $this->setData(self::KEY_TITLES, $titles);
    }

    // @codeCoverageIgnoreEnd

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Tax\Api\Data\TaxRateExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Tax\Api\Data\TaxRateExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Tax\Api\Data\TaxRateExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
