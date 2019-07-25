<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Controller\Adminhtml\Downloadable\File;

use Magento\Framework\Serialize\Serializer\Json;

/**
 * Test for Magento\Downloadable\Controller\Adminhtml\Downloadable\File\Upload
 *
 * @magentoAppArea adminhtml
 */
class UploadTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->jsonSerializer = $this->_objectManager->get(Json::class);
    }

    /**
     * @dataProvider uploadWrongUploadTypeDataProvider
     * @return void
     */
    public function testUploadWrongUploadType($postData)
    {
        $this->getRequest()->setPostValue($postData);
        $this->getRequest()->setMethod('POST');

        $this->dispatch('backend/admin/downloadable_file/upload');

        $body = $this->getResponse()->getBody();
        $result = $this->jsonSerializer->unserialize($body);
        $this->assertEquals('Upload type can not be determined.', $result['error']);
        $this->assertEquals(0, $result['errorcode']);
    }

    /**
     * @return array
     */
    public function uploadWrongUploadTypeDataProvider(): array
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
