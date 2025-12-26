<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\ResourceModel\Address\Attribute\Source;

use Magento\Customer\Model\Config\Share;
use Magento\Customer\Model\ResourceModel\Address\Attribute\Source\CountryWithWebsites;
use Magento\Directory\Model\AllowedCountries;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

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
        $objectManager = new ObjectManager($this);
        $this->countryByWebsite = $objectManager->getObject(
            CountryWithWebsites::class,
            [
                'attrOptionCollectionFactory' => $eavCollectionFactoryMock,
                'attrOptionFactory' => $optionsFactoryMock,
                'countriesFactory' => $this->countriesFactoryMock,
                'allowedCountriesReader' => $this->allowedCountriesMock,
                'storeManager' => $this->storeManagerMock,
                'shareConfig' => $this->shareConfigMock
            ]
        );
    }

    public function testGetAllOptions()
    {
        $website1 = $this->createMock(WebsiteInterface::class);
        $website2 = $this->createMock(WebsiteInterface::class);

        $website1->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(1);
        $website2->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(2);
        $this->shareConfigMock->expects($this->once())
            ->method('isGlobalScope')
            ->willReturn(false);
        $this->storeManagerMock->expects($this->once())
            ->method('getWebsites')
            ->willReturn([$website1, $website2]);
        $collectionMock = $this->createMock(AbstractDb::class);

        $this->allowedCountriesMock->expects($this->exactly(2))
            ->method('getAllowedCountries')
            ->with($this->anything(), $this->anything())
            ->willReturnOnConsecutiveCalls(
                ['AM' => 'AM'],
                ['AM' => 'AM', 'DZ' => 'DZ']
            );
        $this->countriesFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);
        $collectionMock->expects($this->once())
            ->method('addFieldToFilter')
            ->with('country_id', ['in' => ['AM' => 'AM', 'DZ' => 'DZ']])
            ->willReturnSelf();
        $collectionMock->expects($this->once())
            ->method('toOptionArray')
            ->willReturn([
                ['value' => 'AM', 'label' => 'UZ']
            ]);

        $this->assertEquals([
            ['value' => 'AM', 'label' => 'UZ', 'website_ids' => [1, 2]]
        ], $this->countryByWebsite->getAllOptions());
    }
}
