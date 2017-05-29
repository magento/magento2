<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Fixture\OrderInjectable;

use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Fixture\DataSource;

/**
 * EntityId data.
 */
class EntityId extends DataSource
{
    /**
     * @constructor
     * @param FixtureFactory $fixtureFactory
     * @param array $data
     * @param array $params [optional]
     */
    public function __construct(FixtureFactory $fixtureFactory, array $data, array $params = [])
    {
        $this->params = $params;

        if (isset($data['value'])) {
            $this->data = $data['value'];
            return;
        }

        if (!isset($data['products'])) {
            return;
        }
        if (is_string($data['products'])) {
            $products = explode(',', $data['products']);
            foreach ($products as $product) {
                list($fixture, $dataset) = explode('::', trim($product));
                $product = $fixtureFactory->createByCode($fixture, ['dataset' => $dataset]);
                $product->persist();
                $this->data['products'][] = $product;
            }
        } elseif (is_array($data['products'])) {
            $this->data['products'] = $data['products'];
        }
    }
}
