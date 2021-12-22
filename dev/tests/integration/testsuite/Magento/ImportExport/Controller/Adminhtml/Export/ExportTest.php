<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Controller\Adminhtml\Export;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\MysqlMq\Model\QueueManagement;
use Magento\MysqlMq\Model\ResourceModel\Message;
use Magento\TestFramework\MysqlMq\DeleteTopicRelatedMessages;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Test for export controller
 *
 * @see \Magento\ImportExport\Controller\Adminhtml\Export\Export
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class ExportTest extends AbstractBackendController
{
    private const TOPIC_NAME = 'import_export.export';

    /** @var QueueManagement */
    private $queueManagement;

    /** @var Message */
    private $queueMessageResource;

    /** @var SerializerInterface */
    private $json;

    /** @var DeleteTopicRelatedMessages */
    private $deleteTopicRelatedMessages;

    /**
     * @inheridoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->queueManagement = $this->_objectManager->get(QueueManagement::class);
        $this->queueMessageResource = $this->_objectManager->get(Message::class);
        $this->json = $this->_objectManager->get(SerializerInterface::class);
        $this->deleteTopicRelatedMessages = $this->_objectManager->get(DeleteTopicRelatedMessages::class);
        $this->deleteTopicRelatedMessages->execute(self::TOPIC_NAME);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->deleteTopicRelatedMessages->execute(self::TOPIC_NAME);

        parent::tearDown();
    }

    /**
     * @magentoConfigFixture default_store admin/security/use_form_key 1
     *
     * @return void
     */
    public function testExecute(): void
    {
        $expectedSessionMessage = (string)__('Message is added to queue, wait to get your file soon.'
            . ' Make sure your cron job is running to export the file');
        $fileFormat = 'csv';
        $filter = ['price' => [0, 1000]];
        $this->getRequest()->setMethod(Http::METHOD_POST)
            ->setPostValue(['export_filter' => [$filter]])
            ->setParams(
                [
                    'entity' => ProductAttributeInterface::ENTITY_TYPE_CODE,
                    'file_format' => $fileFormat,
                ]
            );
        $this->dispatch('backend/admin/export/export');
        $this->assertSessionMessages($this->containsEqual($expectedSessionMessage));
        $this->assertRedirect($this->stringContains('/export/index/key/'));
        $messages = $this->queueManagement->readMessages('export');
        $this->assertCount(1, $messages);
        $message = reset($messages);
        $this->assertEquals(self::TOPIC_NAME, $message[QueueManagement::MESSAGE_TOPIC]);
        $body = $this->json->unserialize($message[QueueManagement::MESSAGE_BODY]);
        $this->assertStringContainsString(ProductAttributeInterface::ENTITY_TYPE_CODE, $body['file_name']);
        $this->assertEquals($fileFormat, $body['file_format']);
        $actualFilter = $this->json->unserialize($body['export_filter']);
        $this->assertCount(1, $actualFilter);
        $this->assertEquals($filter, reset($actualFilter));
    }
}
