<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Element\Message;

use Magento\Framework\View\Element\Message\MessageConfigurationsPool;
use PHPUnit\Framework\TestCase;

class MessageConfigurationsPoolTest extends TestCase
{
    public function testGetMessageConfiguration()
    {
        $messageConfigurations = [
            'message_identifier_1' => ['renderer' => 'RendererCode'],
            'message_identifier_2' => ['renderer' => 'RendererCode', 'data' => []],
        ];

        $expectedMessageConfigurations = [
            'message_identifier_1' => ['renderer' => 'RendererCode', 'data' => []],
            'message_identifier_2' => ['renderer' => 'RendererCode', 'data' => []],
            'message_identifier_3' => null
        ];

        $pool = new MessageConfigurationsPool($messageConfigurations);

        foreach ($expectedMessageConfigurations as $messageIdentifier => $expectedConfiguration) {
            static::assertSame(
                $expectedConfiguration,
                $pool->getMessageConfiguration($messageIdentifier)
            );
        }
    }

    /**
     * @param array $configuration
     * @dataProvider wrongRenderersDataProvider
     */
    public function testConstructNoRendererException(array $configuration)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Renderer should be defined.');

        new MessageConfigurationsPool($configuration);
    }

    /**
     * @return array
     */
    public static function wrongRenderersDataProvider()
    {
        return [
            [['message_identifier' => []]],
            [['message_identifier' => ['renderer' => 5]]],
            [['message_identifier' => ['renderer' => new \StdClass()]]],
        ];
    }

    /**
     * @param array $configuration
     * @dataProvider wrongDataDataProvider
     */
    public function testConstructWrongDataException(array $configuration)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Data should be of array type.');

        new MessageConfigurationsPool($configuration);
    }

    /**
     * @return array
     */
    public static function wrongDataDataProvider()
    {
        return [
            [
                [
                    'message_identifier' => ['renderer' => 'RendererCode', 'data' => 5]
                ]
            ],
            [
                [
                    'message_identifier' => ['renderer' => 'RendererCode', 'data' => new \StdClass()]
                ]
            ],
        ];
    }
}
