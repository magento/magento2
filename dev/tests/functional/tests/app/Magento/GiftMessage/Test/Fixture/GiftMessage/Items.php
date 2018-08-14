<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GiftMessage\Test\Fixture\GiftMessage;

use Magento\Mtf\Fixture\DataSource;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Prepare Items for GiftMessage.
 */
class Items extends DataSource
{
    /**
     * @constructor
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param array $data [optional]
     */
    public function __construct(FixtureFactory $fixtureFactory, array $params, array $data = [])
    {
        $this->params = $params;
        if (isset($data['datasets'])) {
            $datasets = explode(',', $data['datasets']);
            foreach ($datasets as $dataset) {
                $this->data[] = $fixtureFactory->createByCode('giftMessage', ['dataset' => trim($dataset)]);
            }
        } else {
            $this->data = $data;
        }
    }
}
