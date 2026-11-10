<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Ensures SalesRule API functional tests use the PHPUnit Test.php file suffix.
 */
class ApiFunctionalTestNamingTest extends TestCase
{
    /**
     * @return void
     */
    public function testTotalsInformationApiTestsUsePhpUnitSuffix(): void
    {
        $root = defined('BP') ? BP : dirname(__DIR__, 6);
        $directory = $root . '/dev/tests/api-functional/testsuite/Magento/SalesRule/Api';

        $this->assertDirectoryExists($directory);
        $this->assertFileExists($directory . '/GuestTotalsInformationManagementTest.php');
        $this->assertFileExists($directory . '/TotalsInformationManagementTest.php');
        $this->assertFileDoesNotExist($directory . '/GuestTotalsInformationManagement.php');
        $this->assertFileDoesNotExist($directory . '/TotalsInformationManagement.php');
    }
}
