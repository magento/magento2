<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Element\Message;

use Magento\Framework\View\Element\Message\MessageConfigurationsPool;

class MessageConfigurationsPoolTest extends \PHPUnit\Framework\TestCase
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
        static::expectException(
            '\InvalidArgumentException',
            'Renderer should be defined.'
        );

        new MessageConfigurationsPool($configuration);
    }

    /**
     * @return array
     */
    public function wrongRenderersDataProvider()
    {
        return [
            [['message_identifier' => []]],
            [['message_identifier' => ['renderer' => 5]]],
            [['message_identifier' => ['renderer' => new \StdClass]]],
        ];
    }

    /**
     * @param array $configuration
     * @dataProvider wrongDataDataProvider
     */
    public function testConstructWrongDataException(array $configuration)
    {
        static::expectException(
            '\InvalidArgumentException',
            'Data should be of array type.'
        );

        new MessageConfigurationsPool($configuration);
    }

    /**
     * @return array
     */
    public function wrongDataDataProvider()
    {
        return [
            [
                [
                    'message_identifier' =>
                        ['renderer' => 'RendererCode', 'data' => 5]
                ]
            ],
            [
                [
                    'message_identifier' =>
                        ['renderer' => 'RendererCode', 'data' => new \StdClass]
                ]
            ],
        ];
    }
}
