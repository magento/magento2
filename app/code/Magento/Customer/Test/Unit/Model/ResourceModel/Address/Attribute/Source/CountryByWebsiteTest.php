<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Unit\Model\ResourceModel\Address\Attribute\Source;

use Magento\Customer\Model\ResourceModel\Address\Attribute\Source\CountryByWebsite;
use Magento\Customer\Model\CountryHandler;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\StoreManagerInterface;

class CountryByWebsiteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\CollectionFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    private $countriesFactoryMock;

    /**
     * @var \Magento\Customer\Model\CountryHandler | \PHPUnit_Framework_MockObject_MockObject
     */
    private $countryHandlerMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var CountryByWebsite
     */
    private $countryByWebsite;

    public function setUp()
    {
        $this->countriesFactoryMock =
            $this->getMockBuilder(\Magento\Directory\Model\ResourceModel\Country\CollectionFactory::class)
                ->setMethods(['create'])
                ->disableOriginalConstructor()
                ->getMock();
        $this->countryHandlerMock = $this->getMockBuilder(CountryHandler::class)
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
        $this->storeManagerMock = $this->getMock(StoreManagerInterface::class);
        $this->countryByWebsite = new CountryByWebsite(
            $eavCollectionFactoryMock,
            $optionsFactoryMock,
            $this->countriesFactoryMock,
            $this->countryHandlerMock,
            $this->storeManagerMock
        );
    }

    public function testGetAllOptions()
    {
        $website1 = $this->getMock(WebsiteInterface::class);
        $website2 = $this->getMock(WebsiteInterface::class);

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

        $this->countryHandlerMock->expects($this->exactly(2))
            ->method('getAllowedCountries')
            ->withConsecutive(
                [1, 'website', true],
                [2, 'website', true]
            )
            ->willReturnMap([
                [1, 'website', true, ['AM' => 'AM']],
                [2, 'website', true, ['AM' => 'AM', 'DZ' => 'DZ']]
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
