<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Test\Unit\Ui\Component\Product\Form\Element;

use Magento\CatalogInventory\Ui\Component\Product\Form\Element\UseConfigSettings;
use Magento\Framework\Data\ValueSourceInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class UseConfigSettingsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var ContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var Json|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    /**
     * @var UseConfigSettings
     */
    private $useConfigSettings;

    /**
     * @var \Magento\Framework\Serialize\JsonValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $jsonValidatorMock;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->contextMock = $this->createMock(\Magento\Framework\View\Element\UiComponent\ContextInterface::class);
        $this->serializerMock = $this->createMock(Json::class);
        $this->jsonValidatorMock = $this->getMockBuilder(\Magento\Framework\Serialize\JsonValidator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->useConfigSettings = $this->objectManagerHelper->getObject(
            UseConfigSettings::class,
            [
                'context' => $this->contextMock,
                'serializer' => $this->serializerMock,
                'jsonValidator' => $this->jsonValidatorMock
            ]
        );
    }

    public function testPrepare()
    {
        $processorMock = $this->createMock(\Magento\Framework\View\Element\UiComponent\Processor::class);
        $processorMock->expects($this->atLeastOnce())->method('register');
        $this->contextMock->expects($this->atLeastOnce())->method('getProcessor')->willReturn($processorMock);
        $config = ['valueFromConfig' => 123];
        $this->useConfigSettings->setData('config', $config);
        $this->useConfigSettings->prepare();
        $this->assertEquals($config, $this->useConfigSettings->getData('config'));
    }

    /**
     * @param array $expectedResult
     * @param string|int $sourceValue
     * @param int $serializedCalledNum
     * @param int $isValidCalledNum
     * @dataProvider prepareSourceDataProvider
     */
    public function testPrepareSource(
        array $expectedResult,
        $sourceValue,
        $serializedCalledNum = 0,
        $isValidCalledNum = 0
    ) {
        $processorMock = $this->createMock(\Magento\Framework\View\Element\UiComponent\Processor::class);
        $processorMock->expects($this->atLeastOnce())->method('register');
        $this->contextMock->expects($this->atLeastOnce())->method('getProcessor')->willReturn($processorMock);
        /** @var ValueSourceInterface|\PHPUnit_Framework_MockObject_MockObject $source */
        $source = $this->createMock(ValueSourceInterface::class);
        $source->expects($this->once())
            ->method('getValue')
            ->with($expectedResult['keyInConfiguration'])
            ->willReturn($sourceValue);

        $this->serializerMock->expects($this->exactly($serializedCalledNum))
            ->method('unserialize')
            ->with($sourceValue)
            ->willReturn($expectedResult['valueFromConfig']);

        $this->jsonValidatorMock->expects($this->exactly($isValidCalledNum))
            ->method('isValid')
            ->willReturn(true);

        $config = array_replace($expectedResult, ['valueFromConfig' => $source]);
        $this->useConfigSettings->setData('config', $config);
        $this->useConfigSettings->prepare();

        $this->assertEquals($expectedResult, $this->useConfigSettings->getData('config'));
    }

    public function prepareSourceDataProvider()
    {
        return [
            'valid' => [
                'expectedResult' => [
                    'valueFromConfig' => 2,
                    'keyInConfiguration' => 'validKey'
                ],
                'sourceValue' => 2
            ],
            'serialized' => [
                'expectedResult' => [
                    'valueFromConfig' => ['32000' => 3],
                    'keyInConfiguration' => 'serializedKey',
                    'unserialized' => true
                ],
                'sourceValue' => '{"32000":3}',
                'serialziedCalledNum' => 1,
                'isValidCalledNum' => 1
            ]
        ];
    }
}
