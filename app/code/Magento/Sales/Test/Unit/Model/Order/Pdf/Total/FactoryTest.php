<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order\Pdf\Total;

class FactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Sales\Model\Order\Pdf\Total\Factory
     */
    protected $_factory;

    protected function setUp(): void
    {
        $this->_objectManager = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
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
        $createdModel = $this->getMockBuilder(\Magento\Sales\Model\Order\Pdf\Total\DefaultTotal::class)
            ->setMockClassName((string)$class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_objectManager->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $expectedClassName,
            $arguments
        )->willReturn(
            $createdModel
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
                ['param1', 'param2'], \Magento\Sales\Model\Order\Pdf\Total\DefaultTotal::class,
            ],
            'custom model' => ['custom_class', ['param1', 'param2'], 'custom_class']
        ];
    }

    /**
     */
    public function testCreateException()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('The PDF total model TEST must be or extend \\Magento\\Sales\\Model\\Order\\Pdf\\Total\\DefaultTotal.');

        $this->_factory->create('TEST');
    }
}
