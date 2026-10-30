<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Swagger\Test\Unit\Model;

use Magento\Framework\App\State;
use Magento\Swagger\Model\Config;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /**
     * @var State|MockObject
     */
    private $state;

    protected function setUp(): void
    {
        $this->state = $this->createMock(State::class);
    }

    public function testDisabledInProductionByDefault()
    {
        $this->state->method('getMode')
            ->willReturn(State::MODE_PRODUCTION);
        $config = new Config($this->state);

        self::assertFalse($config->isEnabled());
    }

    /**
     * @param string $mode
     * @param bool $configuredValue
     * @param bool $expectedResult
     */
    #[DataProvider('useCaseProvider')]
    public function testUseCases(string $mode, bool $configuredValue, bool $expectedResult)
    {
        $this->state->method('getMode')
            ->willReturn($mode);
        $config = new Config($this->state, $configuredValue);

        self::assertSame($expectedResult, $config->isEnabled());
    }

    /**
     * Use cases for modes
     *
     * @return array[]
     */
    public static function useCaseProvider(): array
    {
        return [
            [State::MODE_PRODUCTION, false, false],
            [State::MODE_PRODUCTION, true, true],
            [State::MODE_DEVELOPER, true, true],
            [State::MODE_DEVELOPER, false, true],
        ];
    }
}
