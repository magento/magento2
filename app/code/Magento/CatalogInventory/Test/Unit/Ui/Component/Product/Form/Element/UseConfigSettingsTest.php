<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Test\Unit\Ui\Component\Product\Form\Element;

use Magento\CatalogInventory\Ui\Component\Product\Form\Element\UseConfigSettings;
use Magento\Framework\Data\ValueSourceInterface;
use Magento\Framework\Serialize\JsonValidator;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponent\Processor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UseConfigSettingsTest extends TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var ContextInterface|MockObject
     */
    private $contextMock;

    /**
     * @var Json|MockObject
     */
    private $serializerMock;

    /**
     * @var UseConfigSettings
     */
    private $useConfigSettings;

    /**
     * @var JsonValidator|MockObject
     */
    private $jsonValidatorMock;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->contextMock = $this->getMockForAbstractClass(ContextInterface::class);
        $this->serializerMock = $this->createMock(Json::class);
        $this->jsonValidatorMock = $this->getMockBuilder(JsonValidator::class)
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
        $processorMock = $this->createMock(Processor::class);
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
        $processorMock = $this->createMock(Processor::class);
        $processorMock->expects($this->atLeastOnce())->method('register');
        $this->contextMock->expects($this->atLeastOnce())->method('getProcessor')->willReturn($processorMock);
        /** @var ValueSourceInterface|MockObject $source */
        $source = $this->getMockForAbstractClass(ValueSourceInterface::class);
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

    /**
     * @return array
     */
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
