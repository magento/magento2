<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Test\Workaround\Override;

use Magento\TestFramework\Workaround\Override\Config;
use PHPUnit\Framework\TestCase;

/**
 * Provide tests for \Magento\TestFramework\Workaround\Override\Config.
 */
class ConfigTest extends TestCase
{
    /** @var Config */
    private $object;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->object = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getClassConfig', 'getMethodConfig', 'getDataSetConfig'])
            ->getMock();
    }

    /**
     * @dataProvider skipValuesProvider
     *
     * @param bool $skip
     * @param string $skipMessage
     * @return void
     */
    public function testSkippedClass(bool $skip, string $skipMessage): void
    {
        $this->object->expects($this->once())
            ->method('getClassConfig')
            ->with($this)
            ->willReturn(['skip' => $skip, 'skipMessage' => $skipMessage]);
        $config = $this->object->getSkipConfiguration($this);
        $this->assertEquals($skip, $config['skip']);
        if ($skipMessage) {
            $this->assertEquals($skipMessage, $config['skipMessage']);
        }
    }

    /**
     * @dataProvider skipValuesProvider
     *
     * @param bool $skip
     * @param string $skipMessage
     * @return void
     */
    public function testSkippedMethod(bool $skip, string $skipMessage): void
    {
        $this->object->expects($this->once())
            ->method('getClassConfig')
            ->with($this)
            ->willReturn(['skip' => false, 'skipMessage' => null]);
        $this->object->expects($this->once())
            ->method('getMethodConfig')
            ->with($this)
            ->willReturn(['skip' => $skip, 'skipMessage' => $skipMessage]);
        $config = $this->object->getSkipConfiguration($this);
        $this->assertEquals($skip, $config['skip']);
        if ($skipMessage) {
            $this->assertEquals($skipMessage, $config['skipMessage']);
        }
    }

    /**
     * @dataProvider skipValuesProvider
     *
     * @param bool $skip
     * @param string $skipMessage
     * @return void
     */
    public function testSkippedDataSet(bool $skip, string $skipMessage): void
    {
        $this->object->expects($this->once())
            ->method('getClassConfig')
            ->with($this)
            ->willReturn(['skip' => false, 'skipMessage' => null]);
        $this->object->expects($this->once())
            ->method('getMethodConfig')
            ->with($this)
            ->willReturn(['skip' => false, 'skipMessage' => null]);
        $this->object->expects($this->once())
            ->method('getDataSetConfig')
            ->with($this)
            ->willReturn(['skip' => $skip, 'skipMessage' => $skipMessage]);
        $config = $this->object->getSkipConfiguration($this);
        $this->assertEquals($skip, $config['skip']);
        if ($skipMessage) {
            $this->assertEquals($skipMessage, $config['skipMessage']);
        }
    }

    /**
     * @return array
     */
    public static function skipValuesProvider(): array
    {
        return [
            'skipped' => [
                'skip' => true,
                'skipMessage' => 'skip message',
            ],
            'is_not_skipped' => [
                'skip' => false,
                'skipMessage' => '',
            ],
        ];
    }
}
