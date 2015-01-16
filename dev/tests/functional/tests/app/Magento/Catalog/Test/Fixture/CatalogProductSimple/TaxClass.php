<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Fixture\CatalogProductSimple;

use Magento\Tax\Test\Fixture\TaxClass as FixtureTaxClass;
use Mtf\Fixture\FixtureFactory;
use Mtf\Fixture\FixtureInterface;
use Mtf\System\Config;
use Mtf\Util\Protocol\CurlInterface;
use Mtf\Util\Protocol\CurlTransport;
use Mtf\Util\Protocol\CurlTransport\BackendDecorator;

/**
 * Class TaxClass
 *
 * Data keys:
 *  - dataSet
 *  - tax_product_class
 */
class TaxClass implements FixtureInterface
{
    /**
     * Tax class id
     *
     * @var int
     */
    protected $taxClassId;

    /**
     * Tax class name
     *
     * @var string
     */
    protected $data = 'None';

    /**
     * Tax class fixture
     *
     * @var \Magento\Tax\Test\Fixture\TaxClass
     */
    protected $taxClass;

    /**
     * Constructor
     *
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param array|string $data
     */
    public function __construct(FixtureFactory $fixtureFactory, array $params, $data = [])
    {
        $this->params = $params;
        if ((!isset($data['dataSet']) && !isset($data['tax_product_class']))) {
            $this->data = $data;
            return;
        }

        if (isset($data['dataSet'])) {
            $this->taxClass = $fixtureFactory->createByCode('taxClass', ['dataSet' => $data['dataSet']]);
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
     * Set tax class id
     *
     * @param string $taxClassName
     * @return void
     * @throws \Exception
     */
    protected function setTaxClassId($taxClassName)
    {
        $url = $_ENV['app_backend_url'] . 'tax/rule/new/';
        $curl = new BackendDecorator(new CurlTransport(), new Config());
        $curl->addOption(CURLOPT_HEADER, 1);
        $curl->write(CurlInterface::POST, $url, '1.0', [], []);
        $response = $curl->read();
        $curl->close();

        preg_match('~<option value="(\d+)".*>' . $taxClassName . '</option>~', $response, $matches);
        if (!isset($matches[1]) || empty($matches[1])) {
            throw new \Exception('Product tax class id ' . $taxClassName . ' undefined!');
        }

        $this->taxClassId = (int)$matches[1];
    }

    /**
     * Persist custom selections tax classes
     *
     * @return void
     */
    public function persist()
    {
        //
    }

    /**
     * Return prepared data set
     *
     * @param string|null $key
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getData($key = null)
    {
        return $this->data;
    }

    /**
     * Return data set configuration settings
     *
     * @return array
     */
    public function getDataConfig()
    {
        return $this->params;
    }

    /**
     * Return tax class fixture
     *
     * @return FixtureTaxClass
     */
    public function getTaxClass()
    {
        return $this->taxClass;
    }

    /**
     * Return tax class id
     *
     * @return int
     */
    public function getTaxClassId()
    {
        return (int)$this->taxClassId;
    }
}
