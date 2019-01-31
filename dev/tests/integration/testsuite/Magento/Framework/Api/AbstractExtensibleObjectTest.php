<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api;

use Magento\TestModuleExtensionAttributes\Model\Data\FakeRegionFactory;
use Magento\TestModuleExtensionAttributes\Api\Data\FakeRegionExtension;

/**
 * Test for \Magento\Framework\Api\AbstractExtensibleObject
 */
class AbstractExtensibleObjectTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Framework\ObjectManagerInterface */
    private $_objectManager;

    protected function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_objectManager->configure(
            [
                'preferences' => [
                    \Magento\TestModuleExtensionAttributes\Api\Data\FakeAddressInterface::class =>
                        \Magento\TestModuleExtensionAttributes\Model\FakeAddress::class,
                    \Magento\TestModuleExtensionAttributes\Api\Data\FakeRegionInterface::class =>
                        \Magento\TestModuleExtensionAttributes\Model\FakeRegion::class,
                ],
            ]
        );
    }

    /**
     * Test setExtensionAttributes and getExtensionAttributes for \Magento\Framework\Api\AbstractExtensibleObject
     *
     * @param array $expectedDataBefore
     * @param array $expectedDataAfter
     * @dataProvider extensionAttributesDataProvider
     */
    public function testExtensionAttributes($expectedDataBefore, $expectedDataAfter)
    {
        /** @var \Magento\Framework\Api\ExtensionAttributesFactory $regionExtensionFactory */
        $regionExtensionFactory = $this->_objectManager->get(\Magento\Framework\Api\ExtensionAttributesFactory::class);
        /** @var FakeRegionFactory $regionFactory */
        $regionFactory = $this->_objectManager->get(FakeRegionFactory::class);

        /** @var \Magento\TestModuleExtensionAttributes\Model\Data\FakeRegion $region */
        $region = $regionFactory->create();

        $regionCode = 'test_code';
        /** @var \Magento\TestModuleExtensionAttributes\Model\Data\FakeRegionExtensionInterface $regionExtension */
        $regionExtension = $regionExtensionFactory->create(
            \Magento\TestModuleExtensionAttributes\Model\Data\FakeRegion::class,
            ['data' => $expectedDataBefore]
        );
        $region->setRegionCode($regionCode)->setExtensionAttributes($regionExtension);
        $this->assertInstanceOf(\Magento\TestModuleExtensionAttributes\Model\Data\FakeRegion::class, $region);

        $extensionAttributes = $region->getExtensionAttributes();
        $this->assertInstanceOf(FakeRegionExtension::class, $extensionAttributes);
        $this->assertEquals($expectedDataBefore, $extensionAttributes->__toArray());
        $this->assertEquals($regionCode, $region->getRegionCode());

        $regionCode = 'changed_test_code';
        $region->setExtensionAttributes(
            $regionExtensionFactory->create(
                \Magento\TestModuleExtensionAttributes\Model\Data\FakeRegion::class,
                ['data' => $expectedDataAfter]
            )
        )->setRegionCode($regionCode); // change $regionCode to test AbstractExtensibleObject::setData
        $extensionAttributes = $region->getExtensionAttributes();
        $this->assertEquals($expectedDataAfter, $extensionAttributes->__toArray());
        $this->assertEquals($regionCode, $region->getRegionCode());
    }

    public function extensionAttributesDataProvider()
    {
        return [
            'boolean' => [
                [true],
                [false]
            ],
            'integer' => [
                [1],
                [2]
            ],
            'string' => [
                ['test'],
                ['test test']
            ],
            'array' => [
                [[1]],
                [[1, 2]]
            ]
        ];
    }
}
