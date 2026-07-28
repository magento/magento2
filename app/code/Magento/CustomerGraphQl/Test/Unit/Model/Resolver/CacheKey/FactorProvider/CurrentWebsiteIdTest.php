<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Test\Unit\Model\Resolver\CacheKey\FactorProvider;

use Magento\CustomerGraphQl\Model\Resolver\CacheKey\FactorProvider\CurrentWebsiteId;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Store\Api\Data\StoreInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for CurrentWebsiteId cache key factor provider.
 */
class CurrentWebsiteIdTest extends TestCase
{
    /**
     * @var CurrentWebsiteId
     */
    private CurrentWebsiteId $provider;

    /**
     * @var ContextInterface|MockObject
     */
    private ContextInterface|MockObject $contextMock;

    /**
     * @var StoreInterface|MockObject
     */
    private StoreInterface|MockObject $storeMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->storeMock = $this->createMock(StoreInterface::class);
        $this->contextMock = $this->createMock(ContextInterface::class);

        $this->provider = new CurrentWebsiteId();
    }

    /**
     * Test that getFactorName returns the expected constant.
     *
     * @return void
     */
    public function testGetFactorName(): void
    {
        $this->assertEquals('CURRENT_WEBSITE_ID', $this->provider->getFactorName());
    }

    /**
     * Test that getFactorValue returns the website ID from the context store.
     *
     * @return void
     */
    public function testGetFactorValue(): void
    {
        $websiteId = 2;

        $this->storeMock->method('getWebsiteId')
            ->willReturn($websiteId);

        $storeMock = $this->storeMock;
        $extensionAttributesStub = new class ($storeMock) {
            private StoreInterface $store;

            public function __construct(StoreInterface $store)
            {
                $this->store = $store;
            }

            public function getStore(): StoreInterface
            {
                return $this->store;
            }
        };

        $this->contextMock->method('getExtensionAttributes')
            ->willReturn($extensionAttributesStub);

        $this->assertEquals((string)$websiteId, $this->provider->getFactorValue($this->contextMock));
    }
}

