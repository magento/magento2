<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Unit\Model\ResourceModel\Address\Attribute\Source;

use Magento\Customer\Model\Config\Share;
use Magento\Customer\Model\ResourceModel\Address\Attribute\Source\CountryWithWebsites;
use Magento\Directory\Model\AllowedCountries;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\StoreManagerInterface;

class CountryWithWebsitesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\CollectionFactory | \PHPUnit\Framework\MockObject\MockObject
     */
    private $countriesFactoryMock;

    /**
     * @var \Magento\Directory\Model\AllowedCountries | \PHPUnit\Framework\MockObject\MockObject
     */
    private $allowedCountriesMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface | \PHPUnit\Framework\MockObject\MockObject
     */
    private $storeManagerMock;

    /**
     * @var CountryWithWebsites
     */
    private $countryByWebsite;

    /**
     * @var Share | \PHPUnit\Framework\MockObject\MockObject
     */
    private $shareConfigMock;

    protected function setUp(): void
    {
        $this->countriesFactoryMock =
            $this->getMockBuilder(\Magento\Directory\Model\ResourceModel\Country\CollectionFactory::class)
                ->setMethods(['create'])
                ->disableOriginalConstructor()
                ->getMock();
        $this->allowedCountriesMock = $this->getMockBuilder(AllowedCountries::class)
            ->disableOriginalConstructor()
            ->getMock();
        $eavCollectionFactoryMock =
            $this->getMockBuilder(\Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory::class)
                ->disableOriginalConstructor()
                ->getMock();
        $optionsFactoryMock =
            $this->getMockBuilder(\Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->shareConfigMock = $this->getMockBuilder(Share::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->countryByWebsite = new CountryWithWebsites(
            $eavCollectionFactoryMock,
            $optionsFactoryMock,
            $this->countriesFactoryMock,
            $this->allowedCountriesMock,
            $this->storeManagerMock,
            $this->shareConfigMock
        );
    }

    public function testGetAllOptions()
    {
        $website1 = $this->getMockForAbstractClass(WebsiteInterface::class);
        $website2 = $this->getMockForAbstractClass(WebsiteInterface::class);

        $website1->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(1);
        $website2->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(2);
        $this->storeManagerMock->expects($this->once())
            ->method('getWebsites')
            ->willReturn([$website1, $website2]);
        $collectionMock = $this->getMockBuilder(AbstractDb::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->allowedCountriesMock->expects($this->exactly(2))
            ->method('getAllowedCountries')
            ->withConsecutive(
                ['website', 1],
                ['website', 2]
            )
            ->willReturnMap([
                ['website', 1, ['AM' => 'AM']],
                ['website', 2, ['AM' => 'AM', 'DZ' => 'DZ']]
            ]);
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
