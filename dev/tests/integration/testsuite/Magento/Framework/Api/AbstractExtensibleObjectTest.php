<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api;

/**
 * Test for \Magento\Framework\Api\AbstractExtensibleObject
 */
class AbstractExtensibleObjectTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\ObjectManagerInterface */
    private $_objectManager;

    protected function setUp()
    {
        $autoloadWrapper = \Magento\Framework\Autoload\AutoloaderRegistry::getAutoloader();
        $autoloadWrapper->addPsr4('Magento\\Wonderland\\', realpath(__DIR__ . '/_files/Magento/Wonderland'));
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
     * Test setExtensionAttributes and getExtensionAttributes for \Magento\Framework\Api\AbstractExtensibleObject
     *
     * @param array $expectedDataBefore
     * @param array $expectedDataAfter
     * @dataProvider extensionAttributesDataProvider
     */
    public function testExtensionAttributes($expectedDataBefore, $expectedDataAfter)
    {
        /** @var \Magento\Framework\Api\ExtensionAttributesFactory $regionExtensionFactory */
        $regionExtensionFactory = $this->_objectManager->get('Magento\Framework\Api\ExtensionAttributesFactory');
        /** @var \Magento\Wonderland\Model\Data\FakeRegionFactory $regionFactory */
        $regionFactory = $this->_objectManager->get('Magento\Wonderland\Model\Data\FakeRegionFactory');

        /** @var \Magento\Wonderland\Model\Data\FakeRegion $region */
        $region = $regionFactory->create();

        $regionCode = 'test_code';
        /** @var \Magento\Wonderland\Model\Data\FakeRegionExtensionInterface $regionExtension */
        $regionExtension = $regionExtensionFactory->create(
            'Magento\Wonderland\Model\Data\FakeRegion',
            ['data' => $expectedDataBefore]
        );
        $region->setRegionCode($regionCode)->setExtensionAttributes($regionExtension);
        $this->assertInstanceOf('Magento\Wonderland\Model\Data\FakeRegion', $region);

        $extensionAttributes = $region->getExtensionAttributes();
        $this->assertInstanceOf('Magento\Wonderland\Api\Data\FakeRegionExtension', $extensionAttributes);
        $this->assertEquals($expectedDataBefore, $extensionAttributes->__toArray());
        $this->assertEquals($regionCode, $region->getRegionCode());

        $regionCode = 'changed_test_code';
        $region->setExtensionAttributes(
            $regionExtensionFactory->create('Magento\Wonderland\Model\Data\FakeRegion', ['data' => $expectedDataAfter])
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
