<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySourceSelection\Test\Integration;

use Magento\InventorySourceSelectionApi\Api\GetGeoReferenceProviderCodeInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class GetGeoReferenceProviderCodeTest extends TestCase
{
    /**
     * @var GetGeoReferenceProviderCodeInterface
     */
    private $getGeoReferenceProviderCode;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->getGeoReferenceProviderCode =
            Bootstrap::getObjectManager()->get(GetGeoReferenceProviderCodeInterface::class);
    }

    /**
     * @magentoAdminConfigFixture cataloginventory/source_selection_distance_based/provider test_provider
     *
     * @return void
     */
    public function testGetDistanceProviderCode()
    {
        self::assertEquals('test_provider', $this->getGeoReferenceProviderCode->execute());
    }
}
