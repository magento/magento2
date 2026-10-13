<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\Model\Config\Processor;

use Magento\Config\Model\Config\Processor\EnvironmentPlaceholder;
use Magento\Config\Model\Placeholder\PlaceholderFactory;
use Magento\Config\Model\Placeholder\PlaceholderInterface;
use Magento\Framework\Stdlib\ArrayManager;
use PHPUnit\Framework\MockObject\MockObject as Mock;
use PHPUnit\Framework\TestCase;

class EnvironmentPlaceholderTest extends TestCase
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

    /**
     * @var array
     */
    private $env;

    protected function setUp(): void
    {
        $this->placeholderFactoryMock = $this->createMock(PlaceholderFactory::class);
        $this->arrayManagerMock = $this->createMock(ArrayManager::class);
        $this->placeholderMock = $this->createMock(PlaceholderInterface::class);
        $this->env = $_ENV;

        $this->placeholderFactoryMock->expects($this->any())
            ->method('create')
            ->with(PlaceholderFactory::TYPE_ENVIRONMENT)
            ->willReturn($this->placeholderMock);

        $this->model = new EnvironmentPlaceholder(
            $this->placeholderFactoryMock,
            $this->arrayManagerMock
        );
    }

    public function testProcess()
    {
        $_ENV = array_merge(
            $this->env,
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

    protected function tearDown(): void
    {
        $_ENV = $this->env;
    }
}
