<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaStorage\Model\File\Storage;

/**
 * Tests for \Magento\MediaStorage\Model\File\Storage\Response class
 */
class ResponseTest extends \PHPUnit\Framework\TestCase
{
    /**
     * test for \Magento\MediaStorage\Model\File\Storage\Response::sendResponse()
<<<<<<< HEAD
     *
     * @return void
     */
    public function testSendResponse(): void
=======
     */
    public function testSendResponse()
>>>>>>> upstream/2.2-develop
    {
        $expectedHeaders = [
            [
                'field_name' => 'X-Content-Type-Options',
<<<<<<< HEAD
                'field_value' => 'nosniff',
            ],
            [
                'field_name' => 'X-XSS-Protection',
                'field_value' => '1; mode=block',
            ],
            [
                'field_name' => 'X-Frame-Options',
                'field_value' => 'SAMEORIGIN',
=======
                'field_value' => 'nosniff'
            ],
            [
                'field_name' => 'X-XSS-Protection',
                'field_value' => '1; mode=block'
            ],
            [
                'field_name' => 'X-Frame-Options',
                'field_value' => 'SAMEORIGIN'
>>>>>>> upstream/2.2-develop
            ],
        ];
        $filePath = realpath(__DIR__ . '/../../../_files/test_file.html');
        /** @var \Magento\MediaStorage\Model\File\Storage\Response $response */
        $mediaStorageResponse = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\MediaStorage\Model\File\Storage\Response::class
        );
        $mediaStorageResponse->setFilePath($filePath);
        ob_start();
        $mediaStorageResponse->sendResponse();
        ob_end_clean();
        /** @var \Magento\Framework\App\Response\Http $frameworkResponse */
        $frameworkResponse = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\HTTP\PhpEnvironment\Response::class
        );
        $actualHeaders = [];
        foreach ($frameworkResponse->getHeaders() as $responseHeader) {
            $actualHeaders[] = [
                'field_name' => $responseHeader->getFieldName(),
<<<<<<< HEAD
                'field_value' => $responseHeader->getFieldValue(),
=======
                'field_value' => $responseHeader->getFieldValue()
>>>>>>> upstream/2.2-develop
            ];
        }
        foreach ($expectedHeaders as $expected) {
            $this->assertTrue(in_array($expected, $actualHeaders));
        }
    }
}
