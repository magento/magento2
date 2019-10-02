<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CheckoutAgreements\Test\Fixture\CheckoutAgreement;

use Magento\Mtf\Fixture\DataSource;
use Magento\Store\Test\Fixture\Store;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Prepare Stores.
 */
class UsedInForms extends DataSource
{
    /**
     * used in forms fixture.
     *
     * @var Forms[]
     */
    public $forms;

    /**
     * @constructor
     * @param FixtureFactory $fixtureFactory
     * @param array $data
     * @param array $params [optional]
     */
    public function __construct(FixtureFactory $fixtureFactory, array $data, array $params = [])
    {
        $this->params = $params;
        if (isset($data['dataset'])) {
            foreach ($data['dataset'] as $forms) {
                $this->data[] = 'Checkout';
            }
        }
    }

    /**
     * Return array.
     *
     * @return Store[]
     */
    public function getStores()
    {
        return $this->stores;
    }
}
