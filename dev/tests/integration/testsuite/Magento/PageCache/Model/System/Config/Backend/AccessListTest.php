<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\PageCache\Model\System\Config\Backend;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Integration test for AccessList config backend model
 *
 * Tests validation of access list values including:
 * - IP addresses (IPv4 and IPv6)
 * - Hostnames
 * - CIDR notation support
 *
 * @magentoAppArea adminhtml
 */
class AccessListTest extends TestCase
{
    /**
     * @var AccessList
     */
    private $model;

    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->config = $objectManager->create(ScopeConfigInterface::class);
        $this->model = $objectManager->create(AccessList::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        // Clean up any saved configuration changes to prevent test pollution
        if ($this->model !== null) {
            try {
                // If the model was saved to database, delete it
                if ($this->model->getId()) {
                    $this->model->delete();
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                // Suppress Magento exceptions during cleanup to avoid masking test failures
            } catch (\Exception $e) {
                // Suppress any other exceptions during cleanup
            }
        }
        
        parent::tearDown();
    }

    /**
     * Prepare model with value, path and field
     *
     * @param mixed $value
     * @param string $path
     * @return void
     */
    private function prepareModel($value, string $path = 'system/full_page_cache/caching_application/access_list'): void
    {
        $this->model->setValue($value);
        $this->model->setPath($path);
        $this->model->setField('access_list');
    }

    /**
     * Test that valid IP addresses are accepted
     *
     * @param string $value
     */
    #[DataProvider('validIpAddressesDataProvider')]
    public function testValidIpAddresses(string $value): void
    {
        $this->prepareModel($value);
        
        // Should not throw exception
        $result = $this->model->beforeSave();
        
        $this->assertInstanceOf(AccessList::class, $result);
        $this->assertSame($value, $this->model->getValue());
    }

    /**
     * Data provider for valid IP addresses
     *
     * @return array
     */
    public static function validIpAddressesDataProvider(): array
    {
        return [
            'IPv4 localhost' => ['127.0.0.1'],
            'IPv4 private network' => ['192.168.1.1'],
            'IPv4 private network (10.x)' => ['10.0.0.1'],
            'IPv4 private network (172.16.x)' => ['172.16.0.1'],
            'IPv4 public DNS' => ['8.8.8.8'],
            'IPv6 localhost' => ['::1'],
            'IPv6 full notation' => ['2001:0db8:85a3:0000:0000:8a2e:0370:7334'],
            'IPv6 compressed notation' => ['2001:db8::1'],
            'IPv6 link-local' => ['fe80::1'],
            'IPv6 mapped IPv4' => ['::ffff:192.0.2.1'],
        ];
    }

    /**
     * Test that valid hostnames are accepted
     *
     * @param string $value
     */
    #[DataProvider('validHostnamesDataProvider')]
    public function testValidHostnames(string $value): void
    {
        $this->prepareModel($value);
        
        $result = $this->model->beforeSave();
        
        $this->assertInstanceOf(AccessList::class, $result);
        $this->assertSame($value, $this->model->getValue());
    }

    /**
     * Data provider for valid hostnames
     *
     * @return array
     */
    public static function validHostnamesDataProvider(): array
    {
        return [
            'localhost' => ['localhost'],
            'domain' => ['example.com'],
            'subdomain' => ['sub.example.com'],
            'hyphenated hostname' => ['my-server.local'],
            'alphanumeric hostname' => ['server01'],
            'complex hostname' => ['cache-server-01.internal.corp'],
        ];
    }

    /**
     * Test that valid CIDR notation is accepted for IPv4
     *
     * @param string $value
     */
    #[DataProvider('validCidrIpv4DataProvider')]
    public function testValidCidrNotationIpv4(string $value): void
    {
        $this->prepareModel($value);
        
        $result = $this->model->beforeSave();
        
        $this->assertInstanceOf(AccessList::class, $result);
        $this->assertSame($value, $this->model->getValue());
    }

    /**
     * Data provider for valid IPv4 CIDR notation
     *
     * @return array
     */
    public static function validCidrIpv4DataProvider(): array
    {
        return [
            'IPv4 /24 network' => ['192.168.1.0/24'],
            'IPv4 /8 network' => ['10.0.0.0/8'],
            'IPv4 /12 network' => ['172.16.0.0/12'],
            'IPv4 single host /32' => ['192.168.1.0/32'],
            'IPv4 all addresses /0' => ['0.0.0.0/0'],
            'IPv4 /25 subnet' => ['192.168.1.128/25'],
            'IPv4 /30 point-to-point' => ['10.10.10.0/30'],
            'IPv4 /16 network' => ['192.168.0.0/16'],
        ];
    }

    /**
     * Test that valid CIDR notation is accepted for IPv6
     *
     * @param string $value
     */
    #[DataProvider('validCidrIpv6DataProvider')]
    public function testValidCidrNotationIpv6(string $value): void
    {
        $this->prepareModel($value);
        
        $result = $this->model->beforeSave();
        
        $this->assertInstanceOf(AccessList::class, $result);
        $this->assertSame($value, $this->model->getValue());
    }

    /**
     * Data provider for valid IPv6 CIDR notation
     *
     * @return array
     */
    public static function validCidrIpv6DataProvider(): array
    {
        return [
            'IPv6 /32 network' => ['2001:db8::/32'],
            'IPv6 link-local /10' => ['fe80::/10'],
            'IPv6 all addresses /0' => ['::/0'],
            'IPv6 multicast /8' => ['ff00::/8'],
            'IPv6 partial notation with CIDR' => ['2001:0db8::/32'],
        ];
    }

    /**
     * Test that multiple valid values separated by commas are accepted
     *
     * @param string $value
     */
    #[DataProvider('validMultipleValuesDataProvider')]
    public function testValidMultipleValues(string $value): void
    {
        $this->prepareModel($value);
        
        $result = $this->model->beforeSave();
        
        $this->assertInstanceOf(AccessList::class, $result);
        $this->assertSame($value, $this->model->getValue());
    }

    /**
     * Data provider for multiple valid values
     *
     * @return array
     */
    public static function validMultipleValuesDataProvider(): array
    {
        return [
            'IPv4 and hostname' => ['127.0.0.1, localhost'],
            'Multiple IPv4 CIDR' => ['192.168.1.0/24, 10.0.0.0/8'],
            'IPv6, IPv4, and hostname' => ['::1, 127.0.0.1, localhost'],
            'Multiple IPv6 CIDR' => ['2001:db8::/32, fe80::/10'],
            'Mixed types' => ['192.168.1.1, 192.168.1.0/24, example.com'],
            'Complex mixed list' => ['10.0.0.1, 172.16.0.0/12, cache.local, 2001:db8::1'],
        ];
    }

    /**
     * Test that values with extra whitespace are handled correctly
     */
    public function testValuesWithWhitespace(): void
    {
        $value = '  192.168.1.1  ,  localhost  ,  10.0.0.0/8  ';
        
        $this->prepareModel($value);
        
        $result = $this->model->beforeSave();
        
        $this->assertInstanceOf(AccessList::class, $result);
        $this->assertSame($value, $this->model->getValue());
    }

    /**
     * Test that invalid CIDR notation is rejected
     *
     * @param string $value
     */
    #[DataProvider('invalidCidrDataProvider')]
    public function testInvalidCidrNotation(string $value): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('is not valid');
        
        $this->prepareModel($value);
        
        $this->model->beforeSave();
    }

    /**
     * Data provider for invalid CIDR notation
     *
     * Edge cases not covered in unit tests
     *
     * @return array
     */
    public static function invalidCidrDataProvider(): array
    {
        return [
            'IPv4 CIDR > 32 (large)' => ['192.168.1.0/99'],
            'IPv4 CIDR negative' => ['192.168.1.0/-1'],
            'IPv4 CIDR empty' => ['192.168.1.0/'],
            'IPv4 CIDR non-numeric' => ['192.168.1.0/abc'],
        ];
    }

    /**
     * Test that invalid characters are rejected
     *
     * @param string $value
     */
    #[DataProvider('invalidCharactersDataProvider')]
    public function testInvalidCharacters(string $value): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('is not valid');
        
        $this->prepareModel($value);
        
        $this->model->beforeSave();
    }

    /**
     * Data provider for invalid characters
     *
     * Security-focused test cases not covered in unit tests
     *
     * @return array
     */
    public static function invalidCharactersDataProvider(): array
    {
        return [
            'Command injection attempt' => ['192.168.1.1;rm -rf /'],
            'XSS attempt' => ['<script>alert("xss")</script>'],
            'SQL injection attempt' => ['192.168.1.1 OR 1=1'],
            'Command substitution attempt' => ['192.168.1.1`whoami`'],
            'Path traversal attempt' => ['../../etc/passwd'],
        ];
    }

    /**
     * Test that non-string values are rejected
     *
     * @param mixed $value
     */
    #[DataProvider('invalidTypeDataProvider')]
    public function testInvalidValueTypes($value): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('is not valid');
        
        $this->prepareModel($value);
        
        $this->model->beforeSave();
    }

    /**
     * Data provider for invalid value types
     *
     * Additional type validation not covered in unit tests
     *
     * @return array
     */
    public static function invalidTypeDataProvider(): array
    {
        return [
            'Float value' => [123.456],
            'Boolean value' => [true],
            'Array value' => [['192.168.1.1']],
        ];
    }

    /**
     * Test that mixed valid and invalid values in comma-separated list are rejected
     *
     * @param string $value
     */
    #[DataProvider('mixedValidInvalidDataProvider')]
    public function testMixedValidAndInvalidValues(string $value): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('is not valid because of item');
        
        $this->prepareModel($value);
        
        $this->model->beforeSave();
    }

    /**
     * Data provider for mixed valid and invalid values
     *
     * @return array
     */
    public static function mixedValidInvalidDataProvider(): array
    {
        return [
            'Valid IPs with invalid middle' => ['127.0.0.1, invalid@#$, localhost'],
            'Valid CIDR with invalid CIDR' => ['192.168.1.0/24, 10.0.0.0/33'],
            'Valid hosts with XSS attempt' => ['example.com, <script>, localhost'],
            'Valid with invalid characters' => ['::1, invalid!value, 127.0.0.1'],
        ];
    }

    /**
     * Test CIDR boundary values
     *
     * Tests edge cases for CIDR notation boundaries
     */
    public function testCidrBoundaryValues(): void
    {
        $validBoundaries = [
            '192.168.1.0/0',   // Minimum CIDR
            '192.168.1.0/1',
            '192.168.1.0/31',
            '192.168.1.0/32',  // Maximum CIDR for IPv4
        ];

        foreach ($validBoundaries as $value) {
            $this->prepareModel($value);
            
            $result = $this->model->beforeSave();
            $this->assertInstanceOf(AccessList::class, $result, "Failed for CIDR: {$value}");
        }
    }

    /**
     * Test that model can be saved successfully with valid value
     *
     * This test verifies the full save process works with the database
     */
    public function testModelCanBeSaved(): void
    {
        $value = '192.168.1.0/24, localhost, ::1';
        
        $this->prepareModel($value, 'system/full_page_cache/caching_application/access_list_test');
        $this->model->setScope('default');
        $this->model->setScopeId(0);
        
        // Save the model
        $this->model->save();
        
        // Verify it was saved
        $this->assertNotNull($this->model->getId());
        $this->assertSame($value, $this->model->getValue());
    }

    /**
     * Test empty string value behavior
     *
     * Empty values should fall back to default value from parent class
     */
    public function testEmptyValueUsesDefault(): void
    {
        $this->prepareModel('');
        
        $result = $this->model->beforeSave();
        
        $this->assertInstanceOf(AccessList::class, $result);
        // Value should be set to default 'localhost' (from config.xml: system/full_page_cache/default['access_list'])
        $this->assertSame('localhost', $this->model->getValue());
    }
}
