<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Test\Workaround\Override\Fixture\Applier;

use Magento\TestFramework\Workaround\Override\Fixture\Applier\AdminConfigFixture;
use PHPUnit\Framework\TestCase;

/**
 * Provide tests for \Magento\TestFramework\Workaround\Override\Fixture\Applier\AdminConfigFixture
 */
class AdminConfigFixtureTest extends TestCase
{
    /** @var AdminConfigFixture */
    private $object;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->object = new AdminConfigFixture();
    }

    /**
     * @dataProvider annotationsProvider
     *
     * @param string $fixture
     * @param array $attributes
     * @return  void
     */
    public function testIsFixtureMatch(string $fixture, array $attributes): void
    {
        $this->assertTrue($this->invokeIsFixtureMatchMethod($attributes, $fixture));
    }

    /**
     * @return array
     */
    public function annotationsProvider(): array
    {
        return [
            'simple_record' => [
                'current_fixture' => 'section/group/field value',
                'attributes' => [
                    'path' => 'section/group/field',
                    'value' => 'value',
                ],
            ],
            'simple_record_many_spaces' => [
                'current_fixture' => '   section/group/field    value',
                'attributes' => [
                    'path' => 'section/group/field',
                    'value' => 'value',
                ],
            ],
        ];
    }

    /**
     * @dataProvider wrongRecordsProvider
     *
     * @param string $fixture
     * @param array $attributes
     * @return void
     */
    public function testFixtureDoesNotMatch(string $fixture, array $attributes): void
    {
        $this->assertFalse($this->invokeIsFixtureMatchMethod($attributes, $fixture));
    }

    /**
     * @return array
     */
    public function wrongRecordsProvider(): array
    {
        return [
            'another_path_record' => [
                'current_fixture' => 'section/group/another_field value',
                'attributes' => [
                    'path' => 'section/group/field',
                    'value' => 'value',
                ],
            ],
        ];
    }

    /**
     * @dataProvider initFixtureProvider
     *
     * @param array $attributes
     * @param string $expectedValue
     * @return void
     */
    public function testInitConfigFixture(array $attributes, string $expectedValue): void
    {
        $reflectionMethod = new \ReflectionMethod(AdminConfigFixture::class, 'initConfigFixture');
        $reflectionMethod->setAccessible(true);
        $value = $reflectionMethod->invoke($this->object, $attributes);
        $this->assertEquals($expectedValue, $value);
    }

    /**
     * @return array
     */
    public function initFixtureProvider(): array
    {
        return [
            'with_value' => [
                'attributes' => [
                    'path' => 'section/group/field',
                    'value' => 'value',
                ],
                'expected_value' => 'section/group/field value',
            ],
            'with_new_value' => [
                'attributes' => [
                    'path' => 'section/group/field',
                    'newValue' => 'new_value',
                ],
                'expected_value' => 'section/group/field new_value',
            ],
        ];
    }

    /**
     * Invove object method
     *
     * @param array $attributes
     * @param string $fixture
     * @return bool
     */
    private function invokeIsFixtureMatchMethod(array $attributes, string $fixture): bool
    {
        $reflectionMethod = new \ReflectionMethod(AdminConfigFixture::class, 'isFixtureMatch');
        $reflectionMethod->setAccessible(true);
        return $reflectionMethod->invoke($this->object, $attributes, $fixture);
    }
}
