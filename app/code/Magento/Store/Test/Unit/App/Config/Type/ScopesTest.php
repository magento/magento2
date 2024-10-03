<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Unit\App\Config\Type;

use Magento\Framework\App\Config\ConfigSourceInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\App\Config\Type\Scopes;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ScopesTest extends TestCase
{
    /**
     * @var Scopes
     */
    private $unit;

    /**
     * @var MockObject
     */
    private $sourceMock;

    protected function setUp(): void
    {
        $this->sourceMock = $this->getMockBuilder(ConfigSourceInterface::class)
            ->getMock();
        $this->unit = (new ObjectManager($this))->getObject(
            Scopes::class,
            [
                'source' => $this->sourceMock,
            ]
        );
    }

    /**
     * @dataProvider getDataProvider
     */
    public function testGet($path, $expectedResult)
    {
        $configData = [
            'websites' => [
                'admin' => [
                    'website_id' => 0,
                    'code' => 'admin',
                ],
                'default' => [
                    'website_id' => 1,
                    'code' => 'default',
                ],
            ],
            'groups' => [
                '0' => [
                    'group_id' => 0,
                    'code' => 'admin',
                ],
            ],
        ];
        $this->sourceMock->expects($this->once())->method('get')->with('')->willReturn($configData);

        $this->assertEquals($expectedResult, $this->unit->get($path));
    }

    public function testGetConfigWhenDataIsNotPresentInInternalCacheAndNotFound()
    {
        $initConfigData = [
            'websites' => [
                'base' => [
                    'website_id' => 0,
                    'code' => 'base'
                ]
            ]
        ];
        $this->sourceMock->expects($this->any())->method('get')->willReturnMap([
            ['', $initConfigData],
            ['websites/1', null],
        ]);

        $this->assertNull($this->unit->get('websites/1'));
    }

    /**
     * Return path and expected value for test different cases
     *
     * @return array
     */
    public static function getDataProvider()
    {
        return [
            [
                'websites',
                [
                    'admin' => [
                        'website_id' => 0,
                        'code' => 'admin',
                    ],
                    'default' => [
                        'website_id' => 1,
                        'code' => 'default',
                    ]
                ],
            ],
            [
                'websites/admin',
                [
                    'website_id' => 0,
                    'code' => 'admin',
                ],
            ],
            [
                'websites/1',
                [
                    'website_id' => 1,
                    'code' => 'default',
                ],
            ],
            [
                'websites/1/code',
                'default',
            ],
            [
                'groups/0',
                [
                    'group_id' => 0,
                    'code' => 'admin',
                ],
            ],
        ];
    }
}
