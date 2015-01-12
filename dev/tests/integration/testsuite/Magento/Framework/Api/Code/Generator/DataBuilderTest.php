<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api\Code\Generator;

use Magento\Wonderland\Api\Data\FakeAddressInterface;
use Magento\Wonderland\Api\Data\FakeRegionInterface;
use Magento\Wonderland\Model\Data\FakeAddress;
use Magento\Wonderland\Model\Data\FakeRegion;

class DataBuilderTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\ObjectManagerInterface */
    private $_objectManager;

    protected function setUp()
    {
        $autoloadWrapper = \Magento\Framework\Autoload\AutoloaderRegistry::getAutoloader();
        $autoloadWrapper->addPsr4('Magento\\Wonderland\\', realpath(__DIR__ . '/../../_files/Magento/Wonderland'));
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_objectManager->configure(
            [
                'preferences' => [
                    'Magento\Wonderland\Api\Data\FakeAddressInterface' => 'Magento\Wonderland\Model\FakeAddress',
                    'Magento\Wonderland\Api\Data\FakeRegionInterface' => 'Magento\Wonderland\Model\FakeRegion',
                ],
            ]
        );
    }

    /**
     * @dataProvider getBuildersToTest
     */
    public function testBuilders($builderType)
    {
        $builder = $this->_objectManager->create($builderType);
        $this->assertInstanceOf($builderType, $builder);
    }

    public function getBuildersToTest()
    {
        return [
            ['Magento\Checkout\Service\V1\Data\Cart\TotalsBuilder'],
        ];
    }

    public function testDataObjectBuilder()
    {
        $regionBuilder = $this->_objectManager->create('Magento\Wonderland\Model\Data\FakeRegionBuilder');
        $this->assertInstanceOf('\Magento\Wonderland\Model\Data\FakeRegionBuilder', $regionBuilder);
        $region = $regionBuilder->setRegion('test')
            ->setRegionCode('test_code')
            ->setRegionId('test_id')
            ->create();
        $this->assertInstanceOf('\Magento\Wonderland\Model\Data\FakeRegion', $region);
        $this->assertEquals('test', $region->getRegion());
    }

    public function testDataObjectPopulateWithArray()
    {
        $data = $this->getAddressArray();

        /** @var \Magento\Wonderland\Model\Data\FakeAddressBuilder $addressBuilder */
        $addressBuilder = $this->_objectManager->create('Magento\Wonderland\Model\Data\FakeAddressBuilder');
        /** @var \Magento\Wonderland\Api\Data\FakeAddressInterface $address */
        $address = $addressBuilder->populateWithArray($data)
            ->create();
        $this->assertInstanceOf('\Magento\Wonderland\Model\Data\FakeAddress', $address);
        $this->assertEquals('Johnes', $address->getLastname());
        $this->assertNull($address->getCustomAttribute('test'));
        $this->assertEmpty($address->getCustomAttributes());
        $this->assertInstanceOf('\Magento\Wonderland\Model\Data\FakeRegion', $address->getRegion());
        $this->assertInstanceOf('\Magento\Wonderland\Model\Data\FakeRegion', $address->getRegions()[0]);
        $this->assertInstanceOf('\Magento\Wonderland\Model\Data\FakeRegion', $address->getRegions()[1]);
    }

    public function testDataObjectPopulate()
    {
        $data = $this->getAddressArray();

        /** @var \Magento\Wonderland\Model\Data\FakeAddressBuilder $addressBuilder */
        $addressBuilder = $this->_objectManager->create('Magento\Wonderland\Model\Data\FakeAddressBuilder');
        /** @var \Magento\Wonderland\Api\Data\FakeAddressInterface $address */
        $address = $addressBuilder->populateWithArray($data)
            ->create();

        $addressUpdated = $addressBuilder->populate($address)
            ->setCompany('RocketScience')
            ->create();

        $this->assertInstanceOf('\Magento\Wonderland\Model\Data\FakeAddress', $addressUpdated);
        $this->assertEquals('RocketScience', $addressUpdated->getCompany());

        $this->assertEmpty($address->getCustomAttributes());
        $this->assertInstanceOf('\Magento\Wonderland\Model\Data\FakeRegion', $address->getRegion());
        $this->assertInstanceOf('\Magento\Wonderland\Model\Data\FakeRegion', $address->getRegions()[0]);
        $this->assertInstanceOf('\Magento\Wonderland\Model\Data\FakeRegion', $address->getRegions()[1]);
    }

    public function testModelPopulateWithArray()
    {
        $data = $this->getAddressArray();

        /** @var \Magento\Wonderland\Api\Data\FakeAddressDataBuilder $addressBuilder */
        $addressBuilder = $this->_objectManager->create('Magento\Wonderland\Api\Data\FakeAddressDataBuilder');
        /** @var \Magento\Wonderland\Api\Data\FakeAddressInterface $address */
        $address = $addressBuilder->populateWithArray($data)
            ->create();
        $this->assertInstanceOf('\Magento\Wonderland\Api\Data\FakeAddressInterface', $address);
        $this->assertEquals('Johnes', $address->getLastname());
        $this->assertEquals(true, $address->isDefaultShipping());
        $this->assertEquals(false, $address->isDefaultBilling());
        $this->assertNull($address->getCustomAttribute('test'));
        $this->assertInstanceOf('\Magento\Wonderland\Api\Data\FakeRegionInterface', $address->getRegion());
        $this->assertInstanceOf('\Magento\Wonderland\Api\Data\FakeRegionInterface', $address->getRegions()[0]);
        $this->assertInstanceOf('\Magento\Wonderland\Api\Data\FakeRegionInterface', $address->getRegions()[1]);
    }

    public function getAddressArray()
    {
        return [
            FakeAddressInterface::ID => 1,
            FakeAddressInterface::CITY => 'Kiev',
            FakeAddressInterface::REGION => [
                FakeRegionInterface::REGION => 'US',
                FakeRegionInterface::REGION_CODE => 'TX',
                FakeRegionInterface::REGION_ID => '1',
            ],
            FakeAddressInterface::REGIONS => [
                [
                    FakeRegionInterface::REGION => 'US',
                    FakeRegionInterface::REGION_CODE => 'TX',
                    FakeRegionInterface::REGION_ID => '1',
                ], [
                    FakeRegionInterface::REGION => 'US',
                    FakeRegionInterface::REGION_CODE => 'TX',
                    FakeRegionInterface::REGION_ID => '2',
                ],
            ],
            FakeAddressInterface::COMPANY => 'Magento',
            FakeAddressInterface::COUNTRY_ID => 'US',
            FakeAddressInterface::CUSTOMER_ID => '1',
            FakeAddressInterface::FAX => '222',
            FakeAddressInterface::FIRSTNAME => 'John',
            FakeAddressInterface::MIDDLENAME => 'Dow',
            FakeAddressInterface::LASTNAME => 'Johnes',
            FakeAddressInterface::SUFFIX => 'Jr.',
            FakeAddressInterface::POSTCODE => '78757',
            FakeAddressInterface::PREFIX => 'Mr.',
            FakeAddressInterface::STREET => 'Oak rd.',
            FakeAddressInterface::TELEPHONE => '1234567',
            FakeAddressInterface::VAT_ID => '1',
            'test' => 'xxx',
            FakeAddressInterface::DEFAULT_BILLING => false,
            FakeAddressInterface::DEFAULT_SHIPPING => true,
        ];
    }
}
