<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Test\Unit\Ui\Component\Product\Form\Element;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\CatalogInventory\Ui\Component\Product\Form\Element\UseConfigSettings;
use Magento\Framework\Data\ValueSourceInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class UseConfigSettingsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var ContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $processorMock = $this->getMock(
            'Magento\Framework\View\Element\UiComponent\Processor',
            [],
            [],
            '',
            false,
            false
        );
        $processorMock->expects($this->once())
            ->method('register');
        $this->contextMock = $this->getMock('Magento\Framework\View\Element\UiComponent\ContextInterface');
        $this->contextMock->expects($this->any())
            ->method('getProcessor')
            ->willReturn($processorMock);
    }

    /**
     * @return void
     */
    public function testPrepare()
    {
        $config = ['valueFromConfig' => 123];
        $element = $this->getTestedElement($config);
        $element->prepare();
        $this->assertEquals($config, $element->getData('config'));
    }

    /**
     * @return void
     */
    public function testPrepareSource()
    {
        /** @var ValueSourceInterface|\PHPUnit_Framework_MockObject_MockObject $source */
        $source = $this->getMock(ValueSourceInterface::class);
        $source->expects($this->once())
            ->method('getValue')
            ->with('someKey')
            ->willReturn('someData');

        $config = ['valueFromConfig' => $source, 'keyInConfiguration' => 'someKey'];
        $element = $this->getTestedElement($config);
        $element->prepare();

        $expectedResult =['valueFromConfig' => 'someData', 'keyInConfiguration' => 'someKey'];
        $this->assertEquals($expectedResult, $element->getData('config'));
    }

    /**
     * @param array $config
     * @return UseConfigSettings
     */
    protected function getTestedElement(array $config = [])
    {
        return $this->objectManagerHelper->getObject(
            UseConfigSettings::class,
            [
                'context' => $this->contextMock,
                'data' => ['config' => $config]
            ]
        );
    }
}
