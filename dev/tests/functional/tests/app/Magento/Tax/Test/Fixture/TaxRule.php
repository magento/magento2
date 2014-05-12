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

namespace Magento\Tax\Test\Fixture;

use Mtf\Factory\Factory;
use Mtf\Fixture\DataFixture;
use Mtf\System\Config;

/**
 * Class TaxRule
 *
 */
class TaxRule extends DataFixture
{
    /**
     * Initialize data and apply placeholders
     *
     * @param Config $configuration
     * @param array $placeholders
     */
    public function __construct(Config $configuration, array $placeholders = array())
    {
        parent::__construct($configuration, $placeholders);

        $this->_placeholders['us_ca_rate_8_25'] = array($this, '_getTaxRateId');
        $this->_placeholders['uk_full_tax_rate'] = array($this, '_getTaxRateId');
        $this->_placeholders['us_ny_rate_8_1'] = array($this, '_getTaxRateData');
        $this->_placeholders['us_ny_rate_8_375'] = array($this, '_getTaxRateId');
        $this->_placeholders['product_tax_class'] = array($this, '_getTaxClassId');
        $this->_placeholders['customer_tax_class'] = array($this, '_getTaxClassId');
    }

    /**
     * Callback function returns created rate id
     *
     * @param string $dataSetName
     * @return int
     */
    protected function _getTaxRateId($dataSetName)
    {
        $taxRate = Factory::getFixtureFactory()->getMagentoTaxTaxRate();
        $taxRate->switchData($dataSetName);
        return $taxRate->persist()->getTaxRateId();
    }

    /**
     * Callback function returns class id
     *
     * @param string $dataSetName
     * @return mixed
     */
    protected function _getTaxClassId($dataSetName)
    {
        $taxClass = Factory::getFixtureFactory()->getMagentoTaxTaxClass();
        $taxClass->switchData($dataSetName);
        return $taxClass->persist()->getTaxClassId();
    }

    protected function _getTaxRateData($dataSetName)
    {
        $taxClass = Factory::getFixtureFactory()->getMagentoTaxTaxRate();
        $taxClass->switchData($dataSetName);
        return $taxClass->getData('fields');
    }

    /**
     * Get tax rule name
     *
     * @return string
     */
    public function getTaxRuleName()
    {
        return $this->getData('fields/code/value');
    }

    /**
     * Get tax rule priority
     *
     * @return string
     */
    public function getTaxRulePriority()
    {
        return $this->getData('fields/priority/value');
    }

    /**
     * Get tax rule position
     *
     * @return string
     */
    public function getTaxRulePosition()
    {
        return $this->getData('fields/position/value');
    }

    /**
     * Get product/customer tax class
     *
     * @return string|array
     */
    public function getTaxRate()
    {
        return $this->getData('fields/tax_rate');
    }

    /**
     * Get product/customer tax class
     *
     * @param string $taxClass (e.g. product|customer)
     * @return string|array
     */
    public function getTaxClass($taxClass)
    {
        return $this->getData('fields/tax_' . $taxClass . '_class/value');
    }

    /**
     * Create tax rule
     *
     * @return TaxRule
     */
    public function persist()
    {
        Factory::getApp()->magentoTaxCreateTaxRule($this);
        return $this;
    }

    /**
     * Init data
     */
    protected function _initData()
    {
        $this->_data = array(
            'fields' => array(
                'code' => array(
                    'value' => 'Tax Rule %isolation%'
                ),
                'tax_rate' => array(
                    'value' => '1',
                    'input_name' => 'tax_rate[]'
                ),
                'tax_product_class' => array(
                    'value' => '2',
                    'input_name' => 'tax_product_class[]'
                ),
                'tax_customer_class' => array(
                    'value' => '3',
                    'input_name' => 'tax_customer_class[]'
                ),
                'priority' => array(
                    'value' => '0'
                ),
                'position' => array(
                    'value' => '0'
                )
            )
        );

        $this->_repository = Factory::getRepositoryFactory()->getMagentoTaxTaxRule($this->_dataConfig, $this->_data);
    }
}
