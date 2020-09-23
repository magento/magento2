<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Translation\Test\Unit\App\Config\Type;

use Magento\Framework\App\Config\ConfigSourceInterface;
use Magento\Translation\App\Config\Type\Translation;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\Translation\App\Config\Type\Translation
 */
class TranslationTest extends TestCase
{
    /**
     * @var ConfigSourceInterface|MockObject
     */
    private $source;

    /**
     * @var Translation
     */
    private $configType;

    protected function setUp(): void
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
