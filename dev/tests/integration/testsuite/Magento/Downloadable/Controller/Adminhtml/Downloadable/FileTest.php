<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Controller\Adminhtml\Downloadable;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Magento\Downloadable\Controller\Adminhtml\Downloadable\File
 *
 * @magentoAppArea adminhtml
 *
 * phpcs:disable Magento2.Functions.DiscouragedFunction
 * phpcs:disable Magento2.Security.Superglobal
 */
class FileTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        $filePath = dirname(__DIR__) . '/_files/sample.tmp';
        if (is_file($filePath)) {
            unlink($filePath);
        }
    }

    public function testUploadAction()
    {
        copy(dirname(__DIR__) . '/_files/sample.txt', dirname(__DIR__) . '/_files/sample.tmp');
        $_FILES = [
            'samples' => [
                'name' => 'sample.txt',
                'type' => 'text/plain',
                'tmp_name' => dirname(__DIR__) . '/_files/sample.tmp',
                'error' => 0,
                'size' => 0,
            ],
        ];

        $this->getRequest()->setMethod('POST');
        $this->dispatch('backend/admin/downloadable_file/upload/type/samples');
        $body = $this->getResponse()->getBody();
        $result = Bootstrap::getObjectManager()->get(Json::class)->unserialize($body);
        $this->assertEquals(0, $result['error']);
    }

    /**
     * Checks a case when php files are not allowed to upload.
     *
     * @param string $fileName
     * @dataProvider extensionsDataProvider
     */
    public function testUploadProhibitedExtensions($fileName)
    {
        $path = dirname(__DIR__) . '/_files/';
        copy($path . 'sample.txt', $path . 'sample.tmp');

        $_FILES = [
            'samples' => [
                'name' => $fileName,
                'type' => 'text/plain',
                'tmp_name' => $path . 'sample.tmp',
                'error' => 0,
                'size' => 0,
            ],
        ];

        $this->getRequest()->setMethod('POST');
        $this->dispatch('backend/admin/downloadable_file/upload/type/samples');
        $body = $this->getResponse()->getBody();
        $result = Bootstrap::getObjectManager()->get(Json::class)->unserialize($body);

        self::assertArrayHasKey('errorcode', $result);
        self::assertEquals(0, $result['errorcode']);
        self::assertEquals('Disallowed file type.', $result['error']);
    }

    /**
     * Returns different php file extensions.
     *
     * @return array
     */
    public function extensionsDataProvider()
    {
        return [
            ['sample.php'],
            ['sample.php3'],
            ['sample.php4'],
            ['sample.php5'],
            ['sample.php7'],
        ];
    }

    /**
     * @return void
     */
    public function testUploadWrongUploadType(): void
    {
        $postData = [
            'type' => [
                'tmp_name' => 'test.txt',
                'name' => 'result.txt',
            ],
        ];
        $this->getRequest()->setPostValue($postData);

        $this->getRequest()->setMethod('POST');
        $this->dispatch('backend/admin/downloadable_file/upload');
        $body = $this->getResponse()->getBody();
        $result = Bootstrap::getObjectManager()->get(Json::class)->unserialize($body);
        $this->assertEquals('Upload type can not be determined.', $result['error']);
        $this->assertEquals(0, $result['errorcode']);
    }
}
