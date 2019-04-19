<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Model\StoreResolver;

use Magento\TestFramework\Helper\Bootstrap;

class WebsiteTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Website
     */
    private $reader;

    protected function setUp()
    {
        $this->reader = Bootstrap::getObjectManager()->create(Website::class);
    }

    /**
     * Tests retrieving of stores id by passed scope.
     *
     * @param string|null $scopeCode website code
     * @param int $storesCount
     * @magentoDataFixture Magento/Store/_files/second_website_with_two_stores.php
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @dataProvider scopeDataProvider
     */
    public function testGetAllowedStoreIds($scopeCode, $storesCount)
    {
        $this->assertCount($storesCount, $this->reader->getAllowedStoreIds($scopeCode));
    }

    /**
     * Provides scopes and corresponding count of resolved stores.
     *
     * @return array
     */
    public function scopeDataProvider(): array
    {
        return [
            [null, 4],
            ['test', 2]
        ];
    }

    /**
     * Tests retrieving of stores id by passing incorrect scope.
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage The website with code not_exists that was requested wasn't found.
     */
    public function testIncorrectScope()
    {
        $this->reader->getAllowedStoreIds('not_exists');
    }
}
