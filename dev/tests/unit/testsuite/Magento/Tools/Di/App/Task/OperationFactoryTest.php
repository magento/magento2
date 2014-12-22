<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Tools\Di\App\Task;


class OperationFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OperationFactory
     */
    private $factory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

    protected function setUp()
    {
        $this->objectManagerMock = $this->getMockBuilder('Magento\Framework\ObjectManagerInterface')
            ->setMethods([])
            ->getMock();
        $this->factory = new OperationFactory(
            $this->objectManagerMock
        );
    }

    /**
     * @param string $alias
     * @param mixed $arguments
     * @dataProvider aliasesDataProvider
     */
    public function testCreateSuccess($alias, $arguments, $instanceName)
    {
        $operationInstance = $this->getMockBuilder('Magento\Tools\Di\App\Task\OperationInterface')
            ->getMock();

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($instanceName, ['data' => $arguments])
            ->willReturn($operationInstance);

        $this->assertSame($operationInstance, $this->factory->create($alias, $arguments));
    }

    public function testCreateException()
    {
        $notRegisteredOperation = 'coffee';
        $this->setExpectedException(
            'Magento\Tools\Di\App\Task\OperationException',
            sprintf('Unrecognized operation "%s"', $notRegisteredOperation),
            OperationException::UNAVAILABLE_OPERATION
        );
        $this->factory->create($notRegisteredOperation);
    }

    /**
     * @return array
     */
    public function aliasesDataProvider()
    {
        return  [
            [OperationFactory::AREA, [], 'Magento\Tools\Di\App\Task\Operation\Area'],
            [OperationFactory::INTERCEPTION, null, 'Magento\Tools\Di\App\Task\Operation\Interception'],
            [OperationFactory::RELATIONS, 1, 'Magento\Tools\Di\App\Task\Operation\Relations'],
            [OperationFactory::PLUGINS, 'argument', 'Magento\Tools\Di\App\Task\Operation\Plugins'],
        ];
    }

}
