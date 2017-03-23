<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mail\Test\Unit\Template;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $templateMock;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->objectManagerMock = $this->getMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->templateMock = $this->getMock(\Magento\Framework\Mail\TemplateInterface::class);
    }

    /**
     * @param string $expectedArgument
     * @param null|string $namespace
     * @return void
     * @dataProvider getDataProvider
     */
    public function testGet($expectedArgument, $namespace)
    {
        $factory = $this->objectManagerHelper->getObject(
            \Magento\Framework\Mail\Template\Factory::class,
            ['objectManager' => $this->objectManagerMock]
        );

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($expectedArgument, ['data' => ['template_id' => 'identifier']])
            ->willReturn($this->templateMock);

        $this->assertInstanceOf(
            \Magento\Framework\Mail\TemplateInterface::class,
            $factory->get('identifier', $namespace)
        );
    }

    /**
     * @return array
     */
    public function getDataProvider()
    {
        return [
            [
                'expectedArgument' => \Magento\Framework\Mail\TemplateInterface::class,
                'namespace' => null
            ],
            [
                'expectedArgument' => 'Test\Namespace\Implements\TemplateInterface',
                'namespace' => 'Test\Namespace\Implements\TemplateInterface'
            ]
        ];
    }
}
