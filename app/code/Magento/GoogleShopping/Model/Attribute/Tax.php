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
 * @category    Magento
 * @package     Magento_GoogleShopping
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\GoogleShopping\Model\Attribute;

/**
 * Tax attribute model
 *
 * @category   Magento
 * @package    Magento_GoogleShopping
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Tax extends \Magento\GoogleShopping\Model\Attribute\DefaultAttribute
{
    /**
     * Maximum number of tax rates per product supported by google shopping api
     */
    const RATES_MAX = 100;

    /**
     * @var \Magento\Tax\Helper\Data|null
     */
    protected $_taxData = null;

    /**
     * @var \Magento\GoogleCheckout\Helper\Data
     */
    protected $checkoutDataHelper;

    /**
     * Config
     *
     * @var \Magento\GoogleShopping\Model\Config
     */
    protected $_config;

    /**
     * @param \Magento\Model\Context $context
     * @param \Magento\Registry $registry
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\GoogleShopping\Helper\Data $gsData
     * @param \Magento\GoogleShopping\Helper\Product $gsProduct
     * @param \Magento\Catalog\Model\Product\CatalogPrice $catalogPrice
     * @param \Magento\GoogleShopping\Model\Resource\Attribute $resource
     * @param \Magento\GoogleCheckout\Helper\Data $checkoutDataHelper
     * @param \Magento\GoogleShopping\Model\Config $config
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Model\Context $context,
        \Magento\Registry $registry,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\GoogleShopping\Helper\Data $gsData,
        \Magento\GoogleShopping\Helper\Product $gsProduct,
        \Magento\Catalog\Model\Product\CatalogPrice $catalogPrice,
        \Magento\GoogleShopping\Model\Resource\Attribute $resource,
        \Magento\GoogleCheckout\Helper\Data $checkoutDataHelper,
        \Magento\GoogleShopping\Model\Config $config,
        \Magento\Tax\Helper\Data $taxData,
        \Magento\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_config = $config;
        $this->_taxData = $taxData;
        $this->checkoutDataHelper = $checkoutDataHelper;
        parent::__construct(
            $context,
            $registry,
            $productFactory,
            $gsData,
            $gsProduct,
            $catalogPrice,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Set current attribute to entry (for specified product)
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Gdata\Gshopping\Entry $entry
     * @return \Magento\Gdata\Gshopping\Entry
     */
    public function convertAttribute($product, $entry)
    {
        $entry->cleanTaxes();
        if ($this->_taxData->getConfig()->priceIncludesTax()) {
            return $entry;
        }

        $calc = $this->_taxData->getCalculator();
        $customerTaxClass = $calc->getDefaultCustomerTaxClass($product->getStoreId());
        $rates = $calc->getRatesByCustomerAndProductTaxClasses($customerTaxClass, $product->getTaxClassId());
        $targetCountry = $this->_config->getTargetCountry($product->getStoreId());
        $ratesTotal = 0;
        foreach ($rates as $rate) {
            if ($targetCountry == $rate['country']) {
                $regions = $this->_parseRegions($rate['state'], $rate['postcode']);
                $ratesTotal += count($regions);
                if ($ratesTotal > self::RATES_MAX) {
                    throw new \Magento\Core\Exception(__("Google shopping only supports %1 tax rates per product", self::RATES_MAX));
                }
                foreach ($regions as $region) {
                    $entry->addTax(array(
                        'tax_rate' =>     $rate['value'] * 100,
                        'tax_country' =>  empty($rate['country']) ? '*' : $rate['country'],
                        'tax_region' =>   $region
                    ));
                }
            }
        }

        return $entry;
    }

    /**
     * Retrieve array of regions characterized by provided params
     *
     * @param string $state
     * @param string $zip
     * @return string[]
     */
    protected function _parseRegions($state, $zip)
    {
        return (!empty($zip) && $zip != '*') ? $this->_parseZip($zip) : (($state) ? array($state) : array('*'));
    }

    /**
     * Retrieve array of regions characterized by provided zip code
     *
     * @param string $zip
     * @return string[]
     */
    protected function _parseZip($zip)
    {
        if (strpos($zip, '-') == -1) {
            return array($zip);
        } else {
            return $this->checkoutDataHelper->zipRangeToZipPattern($zip);
        }
    }
}
