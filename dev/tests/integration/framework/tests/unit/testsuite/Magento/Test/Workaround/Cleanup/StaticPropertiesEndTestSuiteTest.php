<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Test\Workaround\Cleanup;

use Magento\TestFramework\TestCase\AbstractController;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;

/**
 * @covers \Magento\TestFramework\Workaround\Cleanup\StaticProperties::endTestSuite
 */
class StaticPropertiesEndTestSuiteTest extends TestCase
{
    /**
     * @return void
     */
    public function testEndTestSuiteRestoresWhenEmptySuiteNameIsControllerClass(): void
    {
        require_once __DIR__ . '/StaticPropertiesRestoreProbe.php';

        StaticPropertiesRestoreProbe::$restoreCalls = 0;
        $cleanup = (new \ReflectionClass(StaticPropertiesRestoreProbe::class))->newInstanceWithoutConstructor();
        $cleanup->endTestSuite(TestSuite::empty(AbstractController::class));
        $this->assertSame(1, StaticPropertiesRestoreProbe::$restoreCalls);
    }

    /**
     * @return void
     */
    public function testEndTestSuiteSkipsRestoreWhenEmptySuiteNameIsNotController(): void
    {
        require_once __DIR__ . '/StaticPropertiesRestoreProbe.php';
        require_once __DIR__ . '/TestCasePropertiesTest/DummyTestCase.php';

        StaticPropertiesRestoreProbe::$restoreCalls = 0;
        $cleanup = (new \ReflectionClass(StaticPropertiesRestoreProbe::class))->newInstanceWithoutConstructor();
        $cleanup->endTestSuite(TestSuite::empty(TestCasePropertiesTest\DummyTestCase::class));
        $this->assertSame(0, StaticPropertiesRestoreProbe::$restoreCalls);
    }
}
