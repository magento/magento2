<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Mail\Test\Unit\Template;

use Magento\Framework\Mail\Template\Factory;
use Magento\Framework\Mail\TemplateInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $objectManagerMock;

    /**
     * @var MockObject
     */
    protected $templateMock;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->templateMock = $this->getMockForAbstractClass(TemplateInterface::class);
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
            Factory::class,
            ['objectManager' => $this->objectManagerMock]
        );

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($expectedArgument, ['data' => ['template_id' => 'identifier']])
            ->willReturn($this->templateMock);

        $this->assertInstanceOf(
            TemplateInterface::class,
            $factory->get('identifier', $namespace)
        );
    }

    /**
     * @return array
     */
    public static function getDataProvider()
    {
        return [
            [
                'expectedArgument' => TemplateInterface::class,
                'namespace' => null
            ],
            [
                'expectedArgument' => 'Test\Namespace\Implements\TemplateInterface',
                'namespace' => 'Test\Namespace\Implements\TemplateInterface'
            ]
        ];
    }
}
