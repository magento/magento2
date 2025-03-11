<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Test\Workaround\Override\Fixture\Applier;

use Magento\TestFramework\Workaround\Override\Fixture\Applier\ConfigFixture;
use PHPUnit\Framework\TestCase;

/**
 * Provide tests for \Magento\TestFramework\Workaround\Override\Fixture\Applier\ConfigFixture
 */
class ConfigFixtureTest extends TestCase
{
    /** @var ConfigFixture */
    private $object;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->object = new ConfigFixture();
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
    public static function annotationsProvider(): array
    {
        return [
            'default_scope_record' => [
                'fixture' => 'default/section/group/field value',
                'attributes' => [
                    'path' => 'section/group/field',
                    'value' => 'value',
                    'scopeType' => 'default',
                    'scopeCode' => '',
                ],
            ],
            'default_scope_record_many_spaces' => [
                'fixture' => '   default/section/group/field    value',
                'attributes' => [
                    'path' => 'section/group/field',
                    'value' => 'value',
                    'scopeType' => 'default',
                    'scopeCode' => '',
                ],
            ],
            'current_store_record' => [
                'fixture' => 'current_store section/group/field value',
                'attributes' => [
                    'path' => 'section/group/field',
                    'value' => 'value',
                    'scopeType' => 'store',
                    'scopeCode' => 'current',
                ],
            ],
            'current_store_reocord_many_spaces' => [
                'fixture' => '   current_store    section/group/field value  ',
                'attributes' => [
                    'path' => 'section/group/field',
                    'value' => 'value',
                    'scopeType' => 'store',
                    'scopeCode' => 'current',
                ],
            ],
            'specific_store_record' => [
                'fixture' => 'specific_store section/group/field value',
                'attributes' => [
                    'path' => 'section/group/field',
                    'value' => 'value',
                    'scopeType' => 'store',
                    'scopeCode' => 'specific',
                ],
            ],
            'specific_store_reocord_many_spaces' => [
                'fixture' => '   specific_store   section/group/field    value',
                'attributes' => [
                    'path' => 'section/group/field',
                    'value' => 'value',
                    'scopeType' => 'store',
                    'scopeCode' => 'specific',
                ],
            ],
            'current_website_record' => [
                'fixture' => 'current_website section/group/field value',
                'attributes' => [
                    'path' => 'section/group/field',
                    'value' => 'value',
                    'scopeType' => 'website',
                    'scopeCode' => 'current',
                ],
            ],
            'current_website_record_many_spaces' => [
                'fixture' => '  current_website    section/group/field    value',
                'attributes' => [
                    'path' => 'section/group/field',
                    'value' => 'value',
                    'scopeType' => 'website',
                    'scopeCode' => 'current',
                ],
            ],
            'specific_website_record' => [
                'fixture' => 'base_website section/group/field value',
                'attributes' => [
                    'path' => 'section/group/field',
                    'value' => 'value',
                    'scopeType' => 'website',
                    'scopeCode' => 'base',
                ],
            ],
            'specific_website_record_many_spaces' => [
                'fixture' => ' base_website   section/group/field   value ',
                'attributes' => [
                    'path' => 'section/group/field',
                    'value' => 'value',
                    'scopeType' => 'website',
                    'scopeCode' => 'base',
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
    public static function wrongRecordsProvider(): array
    {
        return [
            'default_scope_record' => [
                'fixture' => 'current_store section/group/field value',
                'attributes' => [
                    'path' => 'section/group/field',
                    'value' => 'value',
                    'scopeType' => 'default',
                    'scopeCode' => '',
                ],
            ],
            'current_store_record' => [
                'fixture' => 'default_store section/group/field value',
                'attributes' => [
                    'path' => 'section/group/field',
                    'value' => 'value',
                    'scopeType' => 'store',
                    'scopeCode' => 'current',
                ],
            ],
            'specific_store_record' => [
                'fixture' => 'current_store section/group/field value',
                'attributes' => [
                    'path' => 'section/group/field',
                    'value' => 'value',
                    'scopeType' => 'store',
                    'scopeCode' => 'specific',
                ],
            ],
            'current_website_record' => [
                'fixture' => 'current_store section/group/field value',
                'attributes' => [
                    'path' => 'section/group/field',
                    'value' => 'value',
                    'scopeType' => 'website',
                    'scopeCode' => 'current',
                ],
            ],
            'specific_website_record' => [
                'fixture' => 'base_website section/group/field value',
                'attributes' => [
                    'path' => 'section/group/field',
                    'value' => 'value',
                    'scopeType' => 'website',
                    'scopeCode' => 'default',
                ],
            ],
            'another_path_record' => [
                'fixture' => 'current_store section/group/another_field value',
                'attributes' => [
                    'path' => 'section/group/field',
                    'value' => 'value',
                    'scopeType' => 'store',
                    'scopeCode' => 'current',
                ],
            ],
            'similar_path' => [
                'fixture' => 'current_store section/group/field_2 value',
                'attributes' => [
                    'path' => 'section/group/field',
                    'value' => 'value',
                    'scopeType' => 'store',
                    'scopeCode' => 'current',
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
        $reflectionMethod = new \ReflectionMethod(ConfigFixture::class, 'initConfigFixture');
        $reflectionMethod->setAccessible(true);
        $value = $reflectionMethod->invoke($this->object, $attributes);
        $this->assertEquals($expectedValue, $value);
    }

    /**
     * @return array
     */
    public static function initFixtureProvider(): array
    {
        return [
            'with_value' => [
                'attributes' => [
                    'path' => 'section/group/field',
                    'value' => 'value',
                    'scopeType' => 'store',
                    'scopeCode' => 'current',
                ],
                'expectedValue' => 'current_store section/group/field value',
            ],
            'with_new_value' => [
                'attributes' => [
                    'path' => 'section/group/field',
                    'newValue' => 'new_value',
                    'scopeType' => 'store',
                    'scopeCode' => 'current',
                ],
                'expectedValue' => 'current_store section/group/field new_value',
            ],
            'default_scope' => [
                'attributes' => [
                    'path' => 'section/group/field',
                    'value' => 'value',
                    'scopeType' => 'default',
                    'scopeCode' => '',
                ],
                'expectedValue' => 'default/section/group/field value',
            ],
            'website_scope' => [
                'attributes' => [
                    'path' => 'section/group/field',
                    'value' => 'value',
                    'scopeType' => 'website',
                    'scopeCode' => 'base',
                ],
                'expectedValue' => 'base_website section/group/field value',
            ],
            'store_scope' => [
                'attributes' => [
                    'path' => 'section/group/field',
                    'value' => 'value',
                    'scopeType' => 'store',
                    'scopeCode' => 'current',
                ],
                'expectedValue' => 'current_store section/group/field value',
            ],
        ];
    }

    /**
     * @dataProvider replaceFixturesProvider
     *
     * @param array $existingFixtures
     * @param array $config
     * @param array $expectedOrder
     * @return void
     */
    public function testReplaceFixtures(array $existingFixtures, array $config, array $expectedOrder): void
    {
        $fixtures = $this->processApply($existingFixtures, $config);
        $this->assertEquals($expectedOrder, $fixtures);
    }

    /**
     * @return array
     */
    public static function replaceFixturesProvider(): array
    {
        return [
            'replace_one_fixture' => [
                'existingFixtures' => [
                    'current_store section/group/field value',
                    'current_store section/group/field_2 another_value',
                ],
                'config' => [
                    [
                        'path' => 'section/group/field',
                        'newValue' => 'new_value',
                        'scopeType' => 'store',
                        'scopeCode' => 'current',
                    ]
                ],
                'expectedOrder' => [
                    'current_store section/group/field new_value',
                    'current_store section/group/field_2 another_value',
                ],
            ],
        ];
    }

    /**
     * @dataProvider addFixturesProvider
     *
     * @param array $existingFixtures
     * @param array $config
     * @param array $expectedOrder
     * @return void
     */
    public function testAddFixture(array $existingFixtures, array $config, array $expectedOrder): void
    {
        $fixtures = $this->processApply($existingFixtures, $config);
        $this->assertEquals($expectedOrder, $fixtures);
    }

    /**
     * @return array
     */
    public static function addFixturesProvider(): array
    {
        return [
            'add_one_fixture' => [
                'existingFixtures' => [
                    'current_store section/group/field value',
                ],
                'config' => [
                    [
                        'path' => 'section/group/field_2',
                        'value' => 'another_value',
                        'scopeType' => 'store',
                        'scopeCode' => 'current',
                    ],
                ],
                'expectedOrder' => [
                    'current_store section/group/field value',
                    'current_store section/group/field_2 another_value',
                ],
            ],
            'add_two_fixtures' => [
                'existingFixtures' => [
                    'current_store section/group/field value',
                ],
                'config' => [
                    [
                        'path' => 'section/group/field_2',
                        'value' => 'another_value',
                        'scopeType' => 'store',
                        'scopeCode' => 'current',
                    ],
                    [
                        'path' => 'section/group/field_3',
                        'value' => 'one_more_value',
                        'scopeType' => 'store',
                        'scopeCode' => 'current',
                    ],
                ],
                'expectedOrder' => [
                    'current_store section/group/field value',
                    'current_store section/group/field_2 another_value',
                    'current_store section/group/field_3 one_more_value',
                ],
            ],
        ];
    }

    /**
     * @dataProvider removeFixturesProvider
     *
     * @param array $existingFixtures
     * @param array $config
     * @param array $expectedOrder
     * @return void
     */
    public function testRemoveFixtures(array $existingFixtures, array $config, array $expectedOrder): void
    {
        $fixtures = $this->processApply($existingFixtures, $config);
        $this->assertEquals($expectedOrder, $fixtures);
    }

    /**
     * @return array
     */
    public static function removeFixturesProvider(): array
    {
        return [
            'remove_one_fixture' => [
                'existingFixtures' => [
                    'current_store section/group/field value',
                    'current_store section/group/field_2 another_value',
                ],
                'config' => [
                    [
                        'path' => 'section/group/field',
                        'scopeType' => 'store',
                        'scopeCode' => 'current',
                        'remove' => true
                    ]
                ],
                'expectedOrder' => [
                    'current_store section/group/field_2 another_value',
                ],
            ],
            'remove_two_fixtures' => [
                'existingFixtures' => [
                    'current_store section/group/field value',
                    'current_store section/group/field_2 another_value',
                    'current_store section/group/field_3 one_more_value',
                ],
                'config' => [
                    [
                        'path' => 'section/group/field',
                        'scopeType' => 'store',
                        'scopeCode' => 'current',
                        'remove' => true,
                    ],
                    [
                        'path' => 'section/group/field_2',
                        'scopeType' => 'store',
                        'scopeCode' => 'current',
                        'remove' => true,
                    ]
                ],
                'expectedOrder' => [
                    'current_store section/group/field_3 one_more_value',
                ],
            ],
        ];
    }

    /**
     * Process apply configurations
     *
     * @param array $existingFixtures
     * @param array $config
     * @return array
     */
    private function processApply(array $existingFixtures, array $config): array
    {
        $this->setConfig($config);
        $fixtures = $this->object->apply($existingFixtures);

        return array_values($fixtures);
    }

    /**
     * Set config to method scope
     *
     * @param array $config
     * @return void
     */
    private function setConfig(array $config): void
    {
        $this->object->setGlobalConfig([]);
        $this->object->setClassConfig([]);
        $this->object->setDataSetConfig([]);
        $this->object->setMethodConfig($config);
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
        $reflectionMethod = new \ReflectionMethod(ConfigFixture::class, 'isFixtureMatch');
        $reflectionMethod->setAccessible(true);
        return $reflectionMethod->invoke($this->object, $attributes, $fixture);
    }
}
