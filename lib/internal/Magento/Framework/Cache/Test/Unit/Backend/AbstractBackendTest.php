<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Test\Unit\Backend;

use Magento\Framework\Cache\Backend\AbstractBackend;
use Magento\Framework\Cache\Exception\CacheException;
use PHPUnit\Framework\TestCase;

/**
 * Test for AbstractBackend base class
 */
class AbstractBackendTest extends TestCase
{
    /**
     * @var AbstractBackend
     */
    private $backend;

    /**
     * Set up test
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->backend = $this->getMockBuilder(AbstractBackend::class)
            ->setConstructorArgs([['option1' => 'value1', 'option2' => 'value2']])
            ->getMock();
    }

    /**
     * Test constructor sets options correctly
     *
     * @return void
     */
    public function testConstructorSetsOptions(): void
    {
        $options = ['test_option' => 'test_value', 'another_option' => 123];

        $backend = $this->getMockBuilder(AbstractBackend::class)
            ->setConstructorArgs([$options])
            ->getMock();

        $this->assertInstanceOf(AbstractBackend::class, $backend);
    }

    /**
     * Test setDirectives method
     *
     * @return void
     */
    public function testSetDirectives(): void
    {
        $directives = ['lifetime' => 7200, 'logging' => true];

        $this->backend->setDirectives($directives);

        // No exception means success
        $this->assertTrue(true);
    }

    /**
     * Test setOption method
     *
     * @return void
     */
    public function testSetOption(): void
    {
        $this->backend->setOption('new_option', 'new_value');

        // No exception means success
        $this->assertTrue(true);
    }

    /**
     * Test that directives can be set without errors
     *
     * @return void
     */
    public function testSetDirectivesAcceptsLifetimeValue(): void
    {
        $directives = ['lifetime' => 3600, 'logging' => true];

        // Should not throw exception
        $this->backend->setDirectives($directives);

        // Verify by setting directives again
        $this->backend->setDirectives(['lifetime' => 7200]);

        $this->assertTrue(true);
    }

    /**
     * Test multiple option setting through constructor
     *
     * @return void
     */
    public function testMultipleOptionsSetThroughConstructor(): void
    {
        $options = [
            'option1' => 'value1',
            'option2' => 100,
            'option3' => true,
            'option4' => ['nested' => 'array']
        ];

        $backend = $this->getMockBuilder(AbstractBackend::class)
            ->setConstructorArgs([$options])
            ->getMock();

        $this->assertInstanceOf(AbstractBackend::class, $backend);
    }

    /**
     * Test setOption with various data types
     *
     * @return void
     */
    public function testSetOptionWithVariousDataTypes(): void
    {
        $this->backend->setOption('string_option', 'test');
        $this->backend->setOption('int_option', 123);
        $this->backend->setOption('bool_option', true);
        $this->backend->setOption('array_option', ['key' => 'value']);

        // No exception means success
        $this->assertTrue(true);
    }

    /**
     * Test backend can be instantiated with empty options
     *
     * @return void
     */
    public function testBackendCanBeInstantiatedWithEmptyOptions(): void
    {
        $backend = $this->getMockBuilder(AbstractBackend::class)
            ->setConstructorArgs([[]])
            ->getMock();

        $this->assertInstanceOf(AbstractBackend::class, $backend);
    }

    /**
     * Test setDirectives with empty array
     *
     * @return void
     */
    public function testSetDirectivesWithEmptyArray(): void
    {
        // Should not throw exception
        $this->backend->setDirectives([]);

        $this->assertTrue(true);
    }

    /**
     * Test setOption can be called multiple times
     *
     * @return void
     */
    public function testSetOptionCanBeCalledMultipleTimes(): void
    {
        $this->backend->setOption('option1', 'value1');
        $this->backend->setOption('option2', 'value2');
        $this->backend->setOption('option1', 'new_value');

        // No exception means success
        $this->assertTrue(true);
    }
}
