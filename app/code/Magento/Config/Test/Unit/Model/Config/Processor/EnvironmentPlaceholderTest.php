<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Model\Config\Processor;

use Magento\Config\Model\Config\Processor\EnvironmentPlaceholder;
use Magento\Config\Model\Placeholder\Environment;
use Magento\Config\Model\Placeholder\PlaceholderFactory;
use Magento\Config\Model\Placeholder\PlaceholderInterface;
use Magento\Framework\Stdlib\ArrayManager;
use \PHPUnit_Framework_MockObject_MockObject as Mock;

class EnvironmentPlaceholderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EnvironmentPlaceholder
     */
    private $model;

    /**
     * @var PlaceholderFactory|Mock
     */
    private $placeholderFactoryMock;

    /**
     * @var ArrayManager|Mock
     */
    private $arrayManagerMock;

    /**
     * @var PlaceholderInterface|Mock
     */
    private $placeholderMock;

    protected function setUp()
    {
        $this->placeholderFactoryMock = $this->getMockBuilder(PlaceholderFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->arrayManagerMock = $this->getMockBuilder(ArrayManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->placeholderMock = $this->getMockBuilder(PlaceholderInterface::class)
            ->getMockForAbstractClass();

        $this->placeholderFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->placeholderMock);

        $this->model = new EnvironmentPlaceholder(
            $this->placeholderFactoryMock,
            $this->arrayManagerMock
        );
    }

    public function testProcess()
    {
        $_ENV = array_merge(
            $_ENV,
            [
                'CONFIG_DEFAULT_TEST' => 1,
                'CONFIG_DEFAULT_TEST2' => 2,
                'BAD_CONFIG' => 3,
            ]
        );

        $this->placeholderMock->expects($this->any())
            ->method('isApplicable')
            ->willReturnMap(
                [
                    ['CONFIG_DEFAULT_TEST', true],
                    ['CONFIG_DEFAULT_TEST2', true],
                    ['BAD_CONFIG', false],
                ]
            );
        $this->placeholderMock->expects($this->any())
            ->method('restore')
            ->willReturnMap(
                [
                    ['CONFIG_DEFAULT_TEST', 'default/test'],
                    ['CONFIG_DEFAULT_TEST2', 'default/test2'],
                ]
            );
        $this->arrayManagerMock->expects($this->any())
            ->method('set')
            ->willReturnMap(
                [
                    ['default/test', [], 1, '/', ['default' => ['test' => 1]]],
                    [
                        'default/test2',
                        [
                            'default' => [
                                'test' => 1
                            ]
                        ],
                        2,
                        '/',
                        [
                            'default' => [
                                'test' => 1,
                                'test2' => 2
                            ]
                        ],
                    ]
                ]
            );

        $this->assertSame(
            [
                'default' => [
                    'test' => 1,
                    'test2' => 2
                ]
            ],
            $this->model->process([])
        );
    }
}
