<?php
namespace Magento\Downloadable\Controller\Adminhtml\Downloadable;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Magento\Downloadable\Controller\Adminhtml\Downloadable\File
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 * @magentoAppArea adminhtml
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
}
