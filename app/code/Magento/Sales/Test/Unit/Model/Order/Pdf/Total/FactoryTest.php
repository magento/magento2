<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order\Pdf\Total;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Sales\Model\Order\Pdf\Total\Factory
     */
    protected $_factory;

    protected function setUp()
    {
        $this->_objectManager = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $this->_factory = new \Magento\Sales\Model\Order\Pdf\Total\Factory($this->_objectManager);
    }

    /**
     * @param mixed $class
     * @param array $arguments
     * @param string $expectedClassName
     * @dataProvider createDataProvider
     */
    public function testCreate($class, $arguments, $expectedClassName)
    {
        $createdModel = $this->getMock(
            'Magento\Sales\Model\Order\Pdf\Total\DefaultTotal',
            [],
            [],
            (string)$class,
            false
        );
        $this->_objectManager->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $expectedClassName,
            $arguments
        )->will(
            $this->returnValue($createdModel)
        );

        $actual = $this->_factory->create($class, $arguments);
        $this->assertSame($createdModel, $actual);
    }

    /**
     * @return array
     */
    public static function createDataProvider()
    {
        return [
            'default model' => [
                null,
                ['param1', 'param2'],
                'Magento\Sales\Model\Order\Pdf\Total\DefaultTotal',
            ],
            'custom model' => ['custom_class', ['param1', 'param2'], 'custom_class']
        ];
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage The PDF total model TEST must be or extend
     * \Magento\Sales\Model\Order\Pdf\Total\DefaultTotal.
     */
    public function testCreateException()
    {
        $this->_factory->create('TEST');
    }
}
