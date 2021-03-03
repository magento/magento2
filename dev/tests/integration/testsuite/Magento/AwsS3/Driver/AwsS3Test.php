<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AwsS3\Driver;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test for AwsS3 adapter.
 */
class AwsS3Test extends \PHPUnit\Framework\TestCase
{
    private const TEST_FILE_PATH = 'test/file.txt';
    private const TEST_FILE_CONTENTS = 'test file contents';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\RemoteStorage\Driver\RemoteDriverInterface
     */
    private $awsDriver;

    public function setUp(): void
    {
        /**
         * Configuration params must be defined in phpunit.xml:
        <const name="TEST_AWS_S3_CONFIG_BUCKET" value="<your bucket name>" />
        <const name="TEST_AWS_S3_CONFIG_REGION" value="<your region>" />
        <const name="TEST_AWS_S3_CONFIG_KEY" value="<your key>" />
        <const name="TEST_AWS_S3_CONFIG_SECRET" value="<your secret>" />
        <const name="TEST_AWS_S3_CONFIG_PREFIX" value="<prefix, usually empty string>" />
         * or in test environment env.php as
         * 'remote_storage' => [
                'driver' => 'aws-s3',
                'config' => [
                    'bucket' => '<your bucket name>',
                    'region' => '<your region>',
                    'credentials' => [
                        'key' => '<your key>',
                        'secret' => '<your secret>'
                    ]
                ]
        ],
         */
        if (defined('TEST_AWS_S3_CONFIG_BUCKET') &&
            defined('TEST_AWS_S3_CONFIG_REGION') &&
            defined('TEST_AWS_S3_CONFIG_KEY') &&
            defined('TEST_AWS_S3_CONFIG_SECRET') &&
            defined('TEST_AWS_S3_CONFIG_PREFIX')
        ) {
            $config = [
                'bucket' => TEST_AWS_S3_CONFIG_BUCKET,
                'region' => TEST_AWS_S3_CONFIG_REGION,
                'credentials' => [
                    'key' => TEST_AWS_S3_CONFIG_KEY,
                    'secret' => TEST_AWS_S3_CONFIG_SECRET,
                ]
            ];
            $prefix = TEST_AWS_S3_CONFIG_PREFIX;
            $this->objectManager = Bootstrap::getObjectManager();
            /** @var \Magento\AwsS3\Driver\AwsS3Factory $driverFactory */
            $driverFactory = $this->objectManager->get(\Magento\AwsS3\Driver\AwsS3Factory::class);
            $this->awsDriver = $driverFactory->createConfigured($config, $prefix);
            $this->awsDriver->filePutContents(self::TEST_FILE_PATH, self::TEST_FILE_CONTENTS);
        } else {
            $this->markTestIncomplete(
                'AWS S3 config not set. See ' . __METHOD__ . ' for details.'
            );
        }
    }

    public function tearDown(): void
    {
        $this->awsDriver->deleteFile(self::TEST_FILE_PATH);
    }

    public function testConnection()
    {
        $this->awsDriver->test();
    }

    public function testGetFile()
    {
        $contents = $this->awsDriver->fileGetContents(self::TEST_FILE_PATH);
        $this->assertEquals('test file contents', $contents);
    }

    public function testFileMetadata()
    {
        $meta = $this->awsDriver->getMetadata(self::TEST_FILE_PATH);
        $this->assertEquals('file', $meta['type']);
        $this->assertEquals('18', $meta['size']);
        $this->assertEquals('test', $meta['dirname']);
        $this->assertEquals('file.txt', $meta['basename']);
        $this->assertEquals('text/plain', $meta['mimetype']);
        $this->assertArrayHasKey('timestamp', $meta);
        $this->assertArrayHasKey('visibility', $meta);
    }

    public function testStat()
    {
        $stat = $this->awsDriver->stat(self::TEST_FILE_PATH);
        $this->assertEquals(18, $stat['size']);
        $this->assertEquals('file', $stat['type']);
        $this->assertArrayHasKey('mtime', $stat);
    }

    public function testCopy()
    {
        $this->awsDriver->copy(self::TEST_FILE_PATH, self::TEST_FILE_PATH . '.copy');
        $this->assertTrue($this->awsDriver->isExists(self::TEST_FILE_PATH . '.copy'));
        $this->assertEquals(
            self::TEST_FILE_CONTENTS,
            $this->awsDriver->fileGetContents(self::TEST_FILE_PATH . '.copy')
        );
        $this->awsDriver->deleteFile(self::TEST_FILE_PATH . '.copy');
    }

    public function testRename()
    {
        $this->awsDriver->rename(self::TEST_FILE_PATH, self::TEST_FILE_PATH . '.renamed');
        $this->assertTrue($this->awsDriver->isExists(self::TEST_FILE_PATH . '.renamed'));
        $this->assertFalse($this->awsDriver->isExists(self::TEST_FILE_PATH));
        $this->assertEquals(
            self::TEST_FILE_CONTENTS,
            $this->awsDriver->fileGetContents(self::TEST_FILE_PATH . '.renamed')
        );
        $this->awsDriver->deleteFile(self::TEST_FILE_PATH . '.renamed');
    }

    public function testDeleteDir()
    {
        $this->awsDriver->deleteDirectory(dirname(self::TEST_FILE_PATH));
        $this->assertFalse($this->awsDriver->isExists(self::TEST_FILE_PATH));
    }

    public function testBoolFunctions()
    {
        $this->assertTrue($this->awsDriver->isDirectory(dirname(self::TEST_FILE_PATH)));
        $this->assertFalse($this->awsDriver->isDirectory(self::TEST_FILE_PATH));
        $this->assertTrue($this->awsDriver->isExists(self::TEST_FILE_PATH));
        $this->assertTrue($this->awsDriver->isExists(dirname(self::TEST_FILE_PATH)));


        $this->assertTrue($this->awsDriver->isFile(self::TEST_FILE_PATH));
        $this->assertFalse($this->awsDriver->isFile(dirname(self::TEST_FILE_PATH)));

        $this->assertTrue($this->awsDriver->isReadable(self::TEST_FILE_PATH));
        $this->assertTrue($this->awsDriver->isReadable(dirname(self::TEST_FILE_PATH)));

        $this->assertTrue($this->awsDriver->isWritable(self::TEST_FILE_PATH));
        $this->assertTrue($this->awsDriver->isWritable(dirname(self::TEST_FILE_PATH)));

    }

    public function testCreateDir()
    {
        $this->awsDriver->createDirectory('test_create');
        $this->assertTrue($this->awsDriver->isExists('test_create'));
        $this->assertTrue($this->awsDriver->isDirectory('test_create'));
        $this->awsDriver->deleteDirectory('test_create');
        $this->assertFalse($this->awsDriver->isExists('test_create'));
    }

}
