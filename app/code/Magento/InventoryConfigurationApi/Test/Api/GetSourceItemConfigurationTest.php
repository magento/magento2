<?php

namespace Magento\InventoryConfigurationApi\Test\Api;

use Magento\TestFramework\TestCase\WebapiAbstract;

class GetSourceItemConfigurationTest extends WebapiAbstract
{
    /**
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryConfigurationApi/Test/_files/source_item_configuration.php
     */
    public function testGetSourceItemConfiguration()
    {
        $this->assertTrue(true);
    }
}
