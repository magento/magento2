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
class AwsS3PerfTest extends \PHPUnit\Framework\TestCase
{
    private const TEST_FILE_DIR = 'perf_test/';
    private const TEST_ITERATIONS = 100;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\RemoteStorage\Driver\RemoteDriverInterface
     */
    private static $awsDriver;

    /**
     * @var int
     */
    private static $isolationIndex = 0;

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
            self::$awsDriver = $driverFactory->createConfigured($config, $prefix, 'predis', array(
                'scheme' => 'tcp',
                'host' => 'localhost',
                'port' => 6379
            ));
        } else {
            $this->markTestIncomplete(
                'AWS S3 config not set. See ' . __METHOD__ . ' for details.'
            );
        }
    }

    private function generateFileName($index) : string
    {
        return self::TEST_FILE_DIR . 'file' . $index . '.png';
    }

    private function uploadFile()
    {
        $contents = file_get_contents(__DIR__ . '/../_files/file.png');
        self::$awsDriver->filePutContents(
            $this->generateFileName(self::$isolationIndex),
            $contents
        );
        self::$isolationIndex++;
    }

    private function downloadFile()
    {
        $contents = self::$awsDriver->fileGetContents($this->generateFileName(rand(0, self::$isolationIndex - 1)));
    }

    private function getFileMetadata()
    {
        $meta = self::$awsDriver->getMetadata($this->generateFileName(rand(0, self::$isolationIndex - 1)));
    }

    private function fileExistsExistingFile()
    {
        self::$awsDriver->isExists($this->generateFileName(rand(0, self::$isolationIndex - 1)));
    }

    private function fileExistsNonExistingFile()
    {
        self::$awsDriver->isExists($this->generateFileName(self::$isolationIndex + 1000));
    }

    /**
     * @param string $testFunctionName
     * @param int $iterations
     * @dataProvider performanceDataProvider()
     */
    public function testPerformance($testFunctionName, $iterations = self::TEST_ITERATIONS)
    {
        $timeStart = microtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            $this->$testFunctionName();
        }
        $timeEnd = microtime(true);
        $execTime = $timeEnd - $timeStart;
        echo 'Testing:' . $testFunctionName . ' = ' . ($execTime / $iterations) . 'ms avg (' . $execTime . ' total) per ' . $iterations . ' iterations';
    }

    public function performanceDataProvider()
    {
        return [
            'File upload ' => ['uploadFile'],
            'File exists' => ['fileExistsExistingFile'],
            'File exists (non existing file)' => ['fileExistsNonExistingFile'],
            'File download' => ['downloadFile'],
            'Get file metadata' => ['getFileMetadata'],
        ];
    }

    public static function tearDownAfterClass(): void
    {
        self::$awsDriver->deleteDirectory(self::TEST_FILE_DIR);
    }
}
