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

namespace Magento\Tax\Model\Calculation;

use Magento\Framework\Exception\CouldNotDeleteException;

/**
 * Tax Rate Model
 *
 * @method \Magento\Tax\Model\Resource\Calculation\Rate _getResource()
 * @method \Magento\Tax\Model\Resource\Calculation\Rate getResource()
 * @method string getTaxCountryId()
 * @method \Magento\Tax\Model\Calculation\Rate setTaxCountryId(string $value)
 * @method int getTaxRegionId()
 * @method \Magento\Tax\Model\Calculation\Rate setTaxRegionId(int $value)
 * @method string getTaxPostcode()
 * @method \Magento\Tax\Model\Calculation\Rate setTaxPostcode(string $value)
 * @method string getCode()
 * @method \Magento\Tax\Model\Calculation\Rate setCode(string $value)
 * @method float getRate()
 * @method \Magento\Tax\Model\Calculation\Rate setRate(float $value)
 * @method int getZipIsRange()
 * @method \Magento\Tax\Model\Calculation\Rate setZipIsRange(int $value)
 * @method int getZipFrom()
 * @method \Magento\Tax\Model\Calculation\Rate setZipFrom(int $value)
 * @method int getZipTo()
 * @method \Magento\Tax\Model\Calculation\Rate setZipTo(int $value)
 */
class Rate extends \Magento\Framework\Model\AbstractModel
{
    /**
     * List of tax titles
     *
     * @var array
     */
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
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Tax\Model\Calculation\Rate\TitleFactory $taxTitleFactory
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Tax\Model\Calculation\Rate\TitleFactory $taxTitleFactory,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_regionFactory = $regionFactory;
        $this->_titleFactory = $taxTitleFactory;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Magento model constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Tax\Model\Resource\Calculation\Rate');
    }

    /**
     * Prepare location settings and tax postcode before save rate
     *
     * @return \Magento\Tax\Model\Calculation\Rate
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _beforeSave()
    {
        $isWrongRange = $this->getZipIsRange() && ($this->getZipFrom() === '' || $this->getZipTo() === '');

        $isEmptyValues = $this->getCode() === '' ||
            $this->getTaxCountryId() === '' ||
            $this->getRate() === '' ||
            ($this->getTaxPostcode() === '' && !$this->getZipIsRange());

        if ($isEmptyValues || $isWrongRange) {
            throw new \Magento\Framework\Model\Exception(__('Please fill all required fields with valid information.'));
        }

        if (!is_numeric($this->getRate()) || $this->getRate() < 0) {
            throw new \Magento\Framework\Model\Exception(__('Rate Percent should be a positive number.'));
        }

        if ($this->getZipIsRange()) {
            $zipFrom = $this->getZipFrom();
            $zipTo = $this->getZipTo();

            if (strlen($zipFrom) > 9 || strlen($zipTo) > 9) {
                throw new \Magento\Framework\Model\Exception(__('Maximum zip code length is 9.'));
            }

            if (!is_numeric($zipFrom) || !is_numeric($zipTo) || $zipFrom < 0 || $zipTo < 0) {
                throw new \Magento\Framework\Model\Exception(__('Zip code should not contain characters other than digits.'));
            }

            if ($zipFrom > $zipTo) {
                throw new \Magento\Framework\Model\Exception(__('Range To should be equal or greater than Range From.'));
            }

            $this->setTaxPostcode($zipFrom . '-' . $zipTo);
        } else {
            $taxPostCode = $this->getTaxPostcode();

            if (strlen($taxPostCode) > 10) {
                $taxPostCode = substr($taxPostCode, 0, 10);
            }

            $this->setTaxPostcode($taxPostCode)->setZipIsRange(null)->setZipFrom(null)->setZipTo(null);
        }

        parent::_beforeSave();
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
    protected function _afterSave()
    {
        $this->saveTitles();
        $this->_eventManager->dispatch('tax_settings_change_after');
        return parent::_afterSave();
    }

    /**
     * Processing object before delete data
     *
     * @return \Magento\Tax\Model\Calculation\Rate
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _beforeDelete()
    {
        if ($this->_isInRule()) {
            throw new CouldNotDeleteException('The tax rate cannot be removed. It exists in a tax rule.');
        }
        return parent::_beforeDelete();
    }

    /**
     * After rate delete
     * redeclared for dispatch tax_settings_change_after event
     *
     * @return \Magento\Tax\Model\Calculation\Rate
     */
    protected function _afterDelete()
    {
        $this->_eventManager->dispatch('tax_settings_change_after');
        return parent::_afterDelete();
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
     * Returns the list of tax titles
     *
     * @return array
     */
    public function getTitles()
    {
        if (is_null($this->_titles)) {
            $this->_titles = $this->getTitleModel()->getCollection()->loadByRateId($this->getId());
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
}
