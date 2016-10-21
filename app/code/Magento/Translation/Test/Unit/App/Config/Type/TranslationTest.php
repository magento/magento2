<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Translation\Test\Unit\App\Config\Type;

use Magento\Authorizenet\Helper\Backend\Data;
use Magento\Framework\App\Cache\Type\Translate;
use Magento\Framework\App\Config\ConfigSourceInterface;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Translation\App\Config\Type\Translation;
use Magento\Framework\DataObject;

/**
 * @covers \Magento\Translation\App\Config\Type\Translation
 */
class TranslationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigSourceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $source;

    /**
     * @var FrontendInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cache;

    /**
     * @var Translation
     */
    private $configType;

    public function setUp()
    {
        $this->source = $this->getMockBuilder(ConfigSourceInterface::class)
            ->getMockForAbstractClass();
        $this->cache = $this->getMockBuilder(FrontendInterface::class)
            ->getMockForAbstractClass();

        $this->configType = new Translation($this->source, $this->cache);
    }

    /**
     * @param bool $isCached
     * @dataProvider getDataProvider
     */
    public function testGet($isCached)
    {
        $path = 'en_US/default';
        $data = [
            'default' => [
                'hello' => 'bonjour'
            ]
        ];

        $this->cache->expects($this->once())
            ->method('load')
            ->with(Translation::CONFIG_TYPE . '/en_US')
            ->willReturn($isCached ? serialize(['en_US' => new DataObject(['en_US' => $data])]) : false);

        if (!$isCached) {
            $this->source->expects($this->once())
                ->method('get')
                ->with('en_US')
                ->willReturn([
                    'default' => [
                        'hello' => 'bonjour'
                    ]
                ]);
            $this->cache
                ->expects($this->once())
                ->method('save')
                ->with(
                    serialize(['en_US' => new DataObject(['en_US' => $data])]),
                    Translation::CONFIG_TYPE . '/en_US',
                    [Translate::TYPE_IDENTIFIER]
                );
        }

        $this->assertEquals(['hello' => 'bonjour'], $this->configType->get($path));
    }

    /**
     * @return array
     */
    public function getDataProvider()
    {
        return [
            [true],
            [false]
        ];
    }
}
