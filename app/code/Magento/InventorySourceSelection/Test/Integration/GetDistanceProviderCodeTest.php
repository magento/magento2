<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySourceSelection\Test\Integration;

use Magento\InventorySourceSelectionApi\Api\GetDistanceProviderCodeInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class GetDistanceProviderCodeTest extends TestCase
{
    /**
     * @var GetDistanceProviderCodeInterface
     */
    private $getDistanceProviderCode;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->getDistanceProviderCode = Bootstrap::getObjectManager()->get(GetDistanceProviderCodeInterface::class);
    }

    /**
     * @magentoAdminConfigFixture cataloginventory/source_selection_distance_based/provider test_provider
     *
     * @return void
     */
    public function testGetDistanceProviderCode()
    {
        self::assertEquals('test_provider', $this->getDistanceProviderCode->execute());
    }
}
