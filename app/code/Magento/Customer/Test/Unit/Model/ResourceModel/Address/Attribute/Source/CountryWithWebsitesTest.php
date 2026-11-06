<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\ResourceModel\Address\Attribute\Source;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Config\Share;
use Magento\Customer\Model\ResourceModel\Address\Attribute\Source\CountryWithWebsites;
use Magento\Directory\Model\AllowedCountries;
use Magento\Directory\Model\ResourceModel\Country\Collection;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory;
use Magento\Framework\App\Request\Http;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests allowed country options across account sharing modes.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CountryWithWebsitesTest extends TestCase
{
    /**
     * @var CollectionFactory|MockObject
     */
    private $countriesFactoryMock;

    /**
     * @var AllowedCountries|MockObject
     */
    private $allowedCountriesMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var CountryWithWebsites
     */
    private $countryByWebsite;

    /**
     * @var Share|MockObject
     */
    private $shareConfigMock;

    /**
     * @var Http|MockObject
     */
    private $requestMock;

    /**
     * @var CustomerRepositoryInterface|MockObject
     */
    private $customerRepositoryMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->countriesFactoryMock =
            $this->getMockBuilder(CollectionFactory::class)
                ->onlyMethods(['create'])
                ->disableOriginalConstructor()
                ->getMock();
        $this->allowedCountriesMock = $this->createMock(AllowedCountries::class);
        $eavCollectionFactoryMock =
            $this->createMock(\Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory::class);
        $optionsFactoryMock =
            $this->createMock(OptionFactory::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->shareConfigMock = $this->createMock(Share::class);
        $this->requestMock = $this->createMock(Http::class);
        $this->customerRepositoryMock = $this->createMock(CustomerRepositoryInterface::class);
        $objectManager = new ObjectManager($this);
        $this->countryByWebsite = $objectManager->getObject(
            CountryWithWebsites::class,
            [
                'attrOptionCollectionFactory' => $eavCollectionFactoryMock,
                'attrOptionFactory' => $optionsFactoryMock,
                'countriesFactory' => $this->countriesFactoryMock,
                'allowedCountriesReader' => $this->allowedCountriesMock,
                'storeManager' => $this->storeManagerMock,
                'shareConfig' => $this->shareConfigMock,
                'request' => $this->requestMock,
                'customerRepository' => $this->customerRepositoryMock
            ]
        );
    }

    /**
     * Verify shared countries and website-specific countries retain their website associations.
     *
     * @param bool $isGlobalScope
     * @return void
     */
    #[DataProvider('sharingScopeDataProvider')]
    public function testGetAllOptions(bool $isGlobalScope): void
    {
        $website1 = $this->createMock(WebsiteInterface::class);
        $website2 = $this->createMock(WebsiteInterface::class);

        $website1->expects($this->atLeastOnce())->method('getId')->willReturn(1);
        $website2->expects($this->atLeastOnce())->method('getId')->willReturn(2);
        $this->shareConfigMock->expects($this->once())->method('isGlobalScope')->willReturn($isGlobalScope);
        $this->requestMock->expects($isGlobalScope ? $this->once() : $this->never())
            ->method('getParam')
            ->willReturn(null);
        $this->customerRepositoryMock->expects($this->never())->method('getById');
        $this->storeManagerMock->expects($this->once())
            ->method('getWebsites')
            ->willReturn([$website1, $website2]);
        $collectionMock = $this->createMock(Collection::class);

        $this->allowedCountriesMock->expects($this->exactly(2))
            ->method('getAllowedCountries')
            ->willReturnMap([
                ['website', 1, ['US' => 'US', 'CA' => 'CA']],
                ['website', 2, ['IN' => 'IN', 'CA' => 'CA']]
            ]);
        $this->countriesFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);
        $collectionMock->expects($this->once())
            ->method('addFieldToFilter')
            ->with('country_id', ['in' => ['US' => 'US', 'CA' => 'CA', 'IN' => 'IN']])
            ->willReturnSelf();
        $collectionMock->expects($this->once())
            ->method('toOptionArray')
            ->willReturn([
                ['value' => '', 'label' => ' '],
                ['value' => 'CA', 'label' => 'Canada'],
                ['value' => 'IN', 'label' => 'India'],
                ['value' => 'US', 'label' => 'United States']
            ]);

        $expected = [
            ['value' => '', 'label' => ' '],
            ['value' => 'CA', 'label' => 'Canada', 'website_ids' => [1, 2]],
            ['value' => 'IN', 'label' => 'India', 'website_ids' => [2]],
            ['value' => 'US', 'label' => 'United States', 'website_ids' => [1]]
        ];
        if ($isGlobalScope) {
            $expected = [
                ['value' => '', 'label' => ' '],
                ['value' => 'CA', 'label' => 'Canada'],
                ['value' => 'IN', 'label' => 'India'],
                ['value' => 'US', 'label' => 'United States']
            ];
        }
        $this->assertSame($expected, $this->countryByWebsite->getAllOptions());
        $this->assertSame($expected, $this->countryByWebsite->getAllOptions());
    }

    /**
     * Global sharing keeps an existing customer's address countries limited to its website.
     *
     * @return void
     */
    public function testGlobalOptionsForExistingCustomer(): void
    {
        $this->shareConfigMock->expects($this->once())->method('isGlobalScope')->willReturn(true);
        $this->requestMock->expects($this->once())->method('getParam')->with('parent_id')->willReturn(42);
        $customer = $this->createMock(CustomerInterface::class);
        $customer->expects($this->once())->method('getWebsiteId')->willReturn(2);
        $this->customerRepositoryMock->expects($this->once())
            ->method('getById')->with(42)->willReturn($customer);
        $this->storeManagerMock->expects($this->never())->method('getWebsites');
        $this->allowedCountriesMock->expects($this->once())
            ->method('getAllowedCountries')
            ->with('website', 2)
            ->willReturn(['IN' => 'IN', 'CA' => 'CA']);
        $collectionMock = $this->createMock(Collection::class);
        $this->countriesFactoryMock->expects($this->once())->method('create')->willReturn($collectionMock);
        $collectionMock->expects($this->once())
            ->method('addFieldToFilter')
            ->with('country_id', ['in' => ['IN' => 'IN', 'CA' => 'CA']])
            ->willReturnSelf();
        $options = [
            ['value' => '', 'label' => ' '],
            ['value' => 'CA', 'label' => 'Canada'],
            ['value' => 'IN', 'label' => 'India']
        ];
        $collectionMock->expects($this->once())->method('toOptionArray')->willReturn($options);

        $this->assertSame($options, $this->countryByWebsite->getAllOptions());
        $this->assertSame($options, $this->countryByWebsite->getAllOptions());
    }

    /**
     * Account sharing modes and customer address form contexts.
     *
     * @return array
     */
    public static function sharingScopeDataProvider(): array
    {
        return [
            'global sharing, no customer context' => [true],
            'website sharing' => [false]
        ];
    }
}
