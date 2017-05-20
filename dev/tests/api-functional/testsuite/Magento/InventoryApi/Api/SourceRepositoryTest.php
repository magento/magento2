<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InventoryApi\Api;

use Magento\TestFramework\TestCase\WebapiAbstract;

class SourceRepositoryTest extends WebapiAbstract
{
    const SERVICE_VERSION = 'V1';
    const SERVICE_NAME = 'inventoryApiSourceRepositoryV1';
    const RESOURCE_PATH = '/V1/inventory/source/';

    /**
     * Create new Inventory Source using Web API and verify it's integrity.
     */
    public function testCreateSource()
    {
        //TODO: Implement testCreateSource
        $this->fail(__METHOD__ . " is not implemented yet.");
    }

    /**
     * Load already existing Inventory Source using Web API and verify it's integrity.
     */
    public function testGetSource()
    {
        //TODO: Implement testGetSource
        $this->fail(__METHOD__ . " is not implemented yet.");
    }

    /**
     * Load and verify integrity of a list of already existing Inventory Sources filtered by Search Criteria.
     */
    public function testGetSourcesList()
    {
        //TODO: Implement testGetSourcesList
        $this->fail(__METHOD__ . " is not implemented yet.");
    }

    /**
     * Update already existing Inventory Source using Web API and verify it's integrity.
     */
    public function testUpdateSource()
    {
        //TODO: Implement testUpdateSource
        $this->fail(__METHOD__ . " is not implemented yet.");
    }

    /**
     * Update already existing Inventory Source, removing carrier links, using Web API and verify it's integrity.
     */
    public function testUpdateSourceWithoutCarriers()
    {
        //TODO: Implement testUpdateSourceWithoutCarriers
        $this->fail(__METHOD__ . " is not implemented yet.");
    }
}
