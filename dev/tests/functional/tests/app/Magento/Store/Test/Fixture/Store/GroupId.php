<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\Fixture\Store;

use Magento\Mtf\Fixture\DataSource;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Store\Test\Fixture\StoreGroup;

/**
 * Prepare StoreGroup for Store.
 */
class GroupId extends DataSource
{
    /**
     * StoreGroup fixture.
     *
     * @var StoreGroup
     */
    protected $storeGroup;

    /**
     * @constructor
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param array $data [optional]
     */
    public function __construct(FixtureFactory $fixtureFactory, array $params, array $data = [])
    {
        $this->params = $params;

        if (isset($data['storeGroup']) && $data['storeGroup'] instanceof StoreGroup) {
            $this->storeGroup = $data['storeGroup'];
            $this->data = $data['storeGroup']->getWebsiteId() . "/" . $data['storeGroup']->getName();
            return;
        }

        if (isset($data['dataset'])) {
            $storeGroup = $fixtureFactory->createByCode('storeGroup', ['dataset' => $data['dataset']]);
            /** @var StoreGroup $storeGroup */
            if (!$storeGroup->getGroupId()) {
                $storeGroup->persist();
            }
            $this->storeGroup = $storeGroup;
            $this->data = $storeGroup->getWebsiteId() . "/" . $storeGroup->getName();
        } elseif (isset($data['fixture'])) {
            $this->storeGroup = $data['fixture'];
            $this->data = $this->storeGroup->getWebsiteId() . "/" . $this->storeGroup->getName();
        }

        if (isset($data['value'])) {
            $this->data = $data['value'];
        }
    }

    /**
     * Return StoreGroup fixture
     *
     * @return StoreGroup
     */
    public function getStoreGroup()
    {
        return $this->storeGroup;
    }
}
