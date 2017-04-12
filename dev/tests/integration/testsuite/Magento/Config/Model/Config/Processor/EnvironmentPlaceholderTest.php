<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Processor;

use Magento\Framework\ObjectManagerInterface;

class EnvironmentPlaceholderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var EnvironmentPlaceholder
     */
    private $model;

    /**
     * @var array
     */
    private $env = [];

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->model = $this->objectManager->get(EnvironmentPlaceholder::class);
        $this->env = $_ENV;
    }

    public function testProcess()
    {
        $_ENV = array_merge(
            $_ENV,
            [
                'CONFIG__DEFAULT__WEB__UNSECURE__BASE_URL' => 'http://expected.local',
                'CONFIG__TEST__TEST__DESIGN__HEADER__WELCOME' => 'Expected header',
                'TEST__TEST__WEB__SECURE__BASE_URL' => 'http://wrong_pattern.local',
                'CONFIG__DEFAULT__GENERAL__REGION__DISPLAY_ALL' => 1
            ]
        );
        $expected = [
            'default' => [
                'web' => [
                    'unsecure' => [
                        'base_url' => 'http://expected.local'
                    ],
                    'secure' => [
                        'base_url' => 'https://original.local'
                    ]
                ],
                'general' => [
                    'region' => [
                        'display_all' => 1
                    ],
                ],
            ],
            'test' => [
                'test' => [
                    'design' => [
                        'header' => [
                            'welcome' => 'Expected header'
                        ]
                    ],
                ],
            ]
        ];
        $config = [
            'default' => [
                'web' => [
                    'unsecure' => [
                        'base_url' => 'http://original.local',
                    ],
                    'secure' => [
                        'base_url' => 'https://original.local'
                    ]
                ]
            ],
            'test' => [
                'test' => [
                    'design' => [
                        'header' => [
                            'welcome' => 'Original header'
                        ]
                    ],
                ],
            ]
        ];

        $this->assertSame(
            $expected,
            $this->model->process($config)
        );
    }

    protected function tearDown()
    {
        $_ENV = $this->env;
    }
}
