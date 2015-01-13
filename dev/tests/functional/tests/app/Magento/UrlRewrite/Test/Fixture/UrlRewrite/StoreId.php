<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewrite\Test\Fixture\UrlRewrite;

use Magento\Store\Test\Fixture\Store;
use Mtf\Fixture\FixtureFactory;
use Mtf\Fixture\FixtureInterface;

/**
 * Class StoreId
 * Store id source
 */
class StoreId implements FixtureInterface
{
    /**
     * Resource data
     *
     * @var string
     */
    protected $data;

    /**
     * Data set configuration settings
     *
     * @var array
     */
    protected $params;

    /**
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param string $data
     */
    public function __construct(FixtureFactory $fixtureFactory, array $params, $data)
    {
        $this->params = $params;
        if (preg_match('`%(.*?)%`', $data, $store)) {
            /** @var Store $storeFixture */
            $storeFixture = $fixtureFactory->createByCode('store', ['dataSet' => $store[1]]);
            if (!$storeFixture->hasData('store_id')) {
                $storeFixture->persist();
            }
            $data = str_replace('%' . $store[1] . '%', $storeFixture->getName(), $data);
        }
        $this->data = $data;
    }

    /**
     * Persist custom selections products
     *
     * @return void
     */
    public function persist()
    {
        //
    }

    /**
     * Return prepared data
     *
     * @param string|null $key [optional]
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
}
