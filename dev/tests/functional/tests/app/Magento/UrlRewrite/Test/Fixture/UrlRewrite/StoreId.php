<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewrite\Test\Fixture\UrlRewrite;

use Magento\Mtf\Fixture\DataSource;
use Magento\Store\Test\Fixture\Store;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Store id source.
 */
class StoreId extends DataSource
{
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
            $storeFixture = $fixtureFactory->createByCode('store', ['dataset' => $store[1]]);
            if (!$storeFixture->hasData('store_id')) {
                $storeFixture->persist();
            }
            $data = str_replace('%' . $store[1] . '%', $storeFixture->getName(), $data);
        }
        $this->data = $data;
    }
}
