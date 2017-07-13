<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
 *
 * @deprecated translation config source was removed.
 */
class TranslationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ConfigSourceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $source;

    /**
     * @var Translation
     */
    private $configType;

    public function setUp()
    {
        $this->source = $this->getMockBuilder(ConfigSourceInterface::class)
            ->getMockForAbstractClass();
        $this->configType = new Translation($this->source);
    }

    public function testGet()
    {
        $path = 'en_US/default';
        $data = [
            'en_US' => [
                'default' => [
                    'hello' => 'bonjour'
                ]
            ]
        ];

        $this->source->expects($this->once())
            ->method('get')
            ->with()
            ->willReturn($data);

        $this->assertEquals(['hello' => 'bonjour'], $this->configType->get($path));
    }
}
