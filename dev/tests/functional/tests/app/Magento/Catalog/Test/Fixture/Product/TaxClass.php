<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Fixture\Product;

use Magento\Mtf\Fixture\DataSource;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Util\Protocol\CurlInterface;
use Magento\Mtf\Util\Protocol\CurlTransport;
use Magento\Tax\Test\Fixture\TaxClass as FixtureTaxClass;
use Magento\Mtf\Util\Protocol\CurlTransport\BackendDecorator;

/**
 * Tax class data source.
 *
 * Data keys:
 *  - dataset
 *  - tax_product_class
 */
class TaxClass extends DataSource
{
    /**
     * Tax class id.
     *
     * @var int
     */
    protected $taxClassId;

    /**
     * Tax class name.
     *
     * @var string
     */
    protected $data = 'None';

    /**
     * Tax class fixture.
     *
     * @var \Magento\Tax\Test\Fixture\TaxClass
     */
    protected $taxClass;

    /**
     * @constructor
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param array|string $data
     */
    public function __construct(FixtureFactory $fixtureFactory, array $params, $data = [])
    {
        $this->params = $params;
        if ((!isset($data['dataset']) && !isset($data['tax_product_class']))) {
            $this->data = $data;
            return;
        }

        if (isset($data['dataset'])) {
            $this->taxClass = $fixtureFactory->createByCode('taxClass', ['dataset' => $data['dataset']]);
            $this->data = $this->taxClass->getClassName();
            if (!$this->taxClass->hasData('id')) {
                $this->taxClass->persist();
            }
        }

        if (isset($data['tax_product_class']) && $data['tax_product_class'] instanceof FixtureTaxClass) {
            $this->taxClass = $data['tax_product_class'];
            $this->data = $this->taxClass->getClassName();
        }

        if ($this->taxClass->hasData('id')) {
            $this->taxClassId = $this->taxClass->getId();
        } else {
            $this->setTaxClassId($this->data);
        }
    }

    /**
     * Set tax class id.
     *
     * @param string $taxClassName
     * @return void
     * @throws \Exception
     */
    protected function setTaxClassId($taxClassName)
    {
        $url = $_ENV['app_backend_url'] . 'tax/rule/new/';
        $config = \Magento\Mtf\ObjectManagerFactory::getObjectManager()->create('Magento\Mtf\Config\DataInterface');
        $curl = new BackendDecorator(new CurlTransport(), $config);
        $curl->addOption(CURLOPT_HEADER, 1);
        $curl->write($url, [], CurlInterface::GET);
        $response = $curl->read();
        $curl->close();

        preg_match('~<option value="(\d+)".*>' . $taxClassName . '</option>~', $response, $matches);
        if (!isset($matches[1]) || empty($matches[1])) {
            throw new \Exception('Product tax class id ' . $taxClassName . ' undefined!');
        }

        $this->taxClassId = (int)$matches[1];
    }

    /**
     * Return tax class fixture.
     *
     * @return FixtureTaxClass
     */
    public function getTaxClass()
    {
        return $this->taxClass;
    }

    /**
     * Return tax class id.
     *
     * @return int
     */
    public function getTaxClassId()
    {
        return (int)$this->taxClassId;
    }
}
