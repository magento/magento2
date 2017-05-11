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

class UseConfigSettingsTest extends \PHPUnit_Framework_TestCase
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

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->contextMock = $this->getMock(\Magento\Framework\View\Element\UiComponent\ContextInterface::class);
        $this->serializerMock = $this->getMock(Json::class);
        $this->useConfigSettings = $this->objectManagerHelper->getObject(
            UseConfigSettings::class,
            [
                'context' => $this->contextMock,
                'serializer' => $this->serializerMock
            ]
        );
    }

    public function testPrepare()
    {
        $processorMock = $this->getMock(
            \Magento\Framework\View\Element\UiComponent\Processor::class,
            [],
            [],
            '',
            false,
            false
        );
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
     * @param int $serializedCallCount
     * @dataProvider prepareSourceDataProvider
     */
    public function testPrepareSource(array $expectedResult, $sourceValue, $serializedCallCount = 0)
    {
        $processorMock = $this->getMock(
            \Magento\Framework\View\Element\UiComponent\Processor::class,
            [],
            [],
            '',
            false,
            false
        );
        $processorMock->expects($this->atLeastOnce())->method('register');
        $this->contextMock->expects($this->atLeastOnce())->method('getProcessor')->willReturn($processorMock);
        /** @var ValueSourceInterface|\PHPUnit_Framework_MockObject_MockObject $source */
        $source = $this->getMock(ValueSourceInterface::class);
        $source->expects($this->once())
            ->method('getValue')
            ->with($expectedResult['keyInConfiguration'])
            ->willReturn($sourceValue);

        $this->serializerMock->expects($this->exactly($serializedCallCount))
            ->method('unserialize')
            ->with($sourceValue)
            ->willReturn($expectedResult['valueFromConfig']);

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
                'serialziedCallCount' => 1
            ]
        ];
    }
}
