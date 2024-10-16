<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Controller\Adminhtml\Downloadable;

use Magento\Framework\Serialize\Serializer\Json;

/**
 * Magento\Downloadable\Controller\Adminhtml\Downloadable\File
 *
 * @magentoAppArea adminhtml
 */
class FileTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->jsonSerializer = $this->_objectManager->get(Json::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $filePath = dirname(__DIR__) . '/_files/sample.tmp';
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        if (is_file($filePath)) {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
            unlink($filePath);
        }
    }

    public function testUploadAction()
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        copy(dirname(__DIR__) . '/_files/sample.txt', dirname(__DIR__) . '/_files/sample.tmp');
        // phpcs:ignore Magento2.Security.Superglobal
        $_FILES = [
            'samples' => [
                'name' => 'sample.txt',
                'type' => 'text/plain',
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                'tmp_name' => dirname(__DIR__) . '/_files/sample.tmp',
                'error' => 0,
                'size' => 0,
            ],
        ];

        $this->getRequest()->setMethod('POST');
        $this->dispatch('backend/admin/downloadable_file/upload/type/samples');
        $body = $this->getResponse()->getBody();
        $result = $this->jsonSerializer->unserialize($body);
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
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $path = dirname(__DIR__) . '/_files/';
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        copy($path . 'sample.txt', $path . 'sample.tmp');
        // phpcs:ignore Magento2.Security.Superglobal
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
        $result = $this->jsonSerializer->unserialize($body);

        self::assertArrayHasKey('errorcode', $result);
        self::assertEquals(0, $result['errorcode']);
        self::assertEquals('Disallowed file type.', $result['error']);
    }

    /**
     * Returns different php file extensions.
     *
     * @return array
     */
    public static function extensionsDataProvider()
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
     * @dataProvider uploadWrongUploadTypeDataProvider
     * @return void
     */
    public function testUploadWrongUploadType($postData): void
    {
        $this->getRequest()->setPostValue($postData);
        $this->getRequest()->setMethod('POST');

        $this->dispatch('backend/admin/downloadable_file/upload');

        $body = $this->getResponse()->getBody();
        $result = $this->jsonSerializer->unserialize($body);
        $this->assertEquals('Upload type can not be determined.', $result['error']);
        $this->assertEquals(0, $result['errorcode']);
    }

    public static function uploadWrongUploadTypeDataProvider(): array
    {
        return [
            [
                ['type' => 'test'],
            ],
            [
                [
                    'type' => [
                        'type1' => 'test',
                    ],
                ],
            ],
        ];
    }
}
