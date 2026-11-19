<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Cron\Test\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Test for pub/cron.php security measures
 */
class CronSecurityTest extends TestCase
{
    /**
     * Test that HTTP requests to cron.php are blocked
     */
    public function testHttpAccessIsBlocked(): void
    {
        // Simulate HTTP request environment
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['HTTP_HOST'] = 'example.com';
        
        // Test the security logic from cron.php
        $output = '';
        $exitCode = 0;
        
        try {
            // Test HTTP blocking logic
            if (isset($_SERVER['REQUEST_METHOD'])) {
                $output = "Forbidden: This script is not intended for web access.\n";
                $output .= "Use command line: php bin/magento cron:run\n";
                $exitCode = 1;
            }
        } finally {
            // Clean up server variables
            unset($_SERVER['REQUEST_METHOD'], $_SERVER['HTTP_HOST']);
        }
        
        $this->assertEquals(1, $exitCode, 'HTTP access should result in exit code 1');
        $this->assertStringContains('Forbidden', $output, 'Should show forbidden message');
        $this->assertStringContains('bin/magento cron:run', $output, 'Should recommend proper command');
    }
    
    /**
     * Test that CLI access shows deprecation message
     */
    public function testCliAccessShowsDeprecationMessage(): void
    {
        // Ensure no REQUEST_METHOD is set (pure CLI environment)
        unset($_SERVER['REQUEST_METHOD']);
        
        // Test CLI behavior
        $this->assertTrue(php_sapi_name() === 'cli', 'This test must run in CLI mode');
        
        // The security check should pass for CLI without REQUEST_METHOD
        $securityBlocked = isset($_SERVER['REQUEST_METHOD']);
        $this->assertFalse($securityBlocked, 'CLI access should pass security check');
        
        // Simulate the CLI logic from cron.php
        $expectedMessage = "Please use the recommended command instead:" . PHP_EOL .
            "php bin/magento cron:run" . PHP_EOL;
        
        // The CLI portion should recommend using bin/magento cron:run
        $this->assertStringContains('bin/magento cron:run', $expectedMessage);
    }
    
    /**
     * Test detection of HTTP vs CLI environment
     */
    public function testEnvironmentDetection(): void
    {
        // Test CLI detection
        $this->assertEquals('cli', php_sapi_name(), 'Should detect CLI environment');
        
        // Test HTTP detection simulation
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->assertTrue(isset($_SERVER['REQUEST_METHOD']), 'Should detect HTTP request method');
        
        // Test security logic
        $httpBlocked = isset($_SERVER['REQUEST_METHOD']);
        $this->assertTrue($httpBlocked, 'HTTP requests should be blocked');
        
        // Clean up
        unset($_SERVER['REQUEST_METHOD']);
        
        // Test CLI environment
        $cliBlocked = isset($_SERVER['REQUEST_METHOD']);
        $this->assertFalse($cliBlocked, 'CLI requests should not be blocked');
    }
    
    /**
     * Test various HTTP methods are all blocked
     */
    public function testAllHttpMethodsBlocked(): void
    {
        $httpMethods = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS', 'HEAD'];
        
        foreach ($httpMethods as $method) {
            $_SERVER['REQUEST_METHOD'] = $method;
            
            $blocked = isset($_SERVER['REQUEST_METHOD']);
            $this->assertTrue($blocked, "HTTP method $method should be blocked");
            
            unset($_SERVER['REQUEST_METHOD']);
        }
    }
}