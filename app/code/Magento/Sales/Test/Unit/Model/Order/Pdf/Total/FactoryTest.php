<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Pdf\Total;

use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Model\Order\Pdf\Total\DefaultTotal;
use Magento\Sales\Model\Order\Pdf\Total\Factory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    /**
     * @var MockObject|ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var Factory
     */
    protected $_factory;

    protected function setUp(): void
    {
        $this->_objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->_factory = new Factory($this->_objectManager);
    }

    /**
     * @param mixed $class
     * @param array $arguments
     * @param string $expectedClassName
     * @dataProvider createDataProvider
     */
    public function testCreate($class, $arguments, $expectedClassName)
    {
        $createdModel = $this->getMockBuilder(DefaultTotal::class)
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
                ['param1', 'param2'],
                DefaultTotal::class,
            ],
            'custom model' => ['custom_class', ['param1', 'param2'], 'custom_class']
        ];
    }

    public function testCreateException()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage(
            'The PDF total model TEST must be or extend \Magento\Sales\Model\Order\Pdf\Total\DefaultTotal.'
        );
        $this->_factory->create('TEST');
    }
}
