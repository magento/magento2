<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Controller\Adminhtml\Export;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Locale\ResolverInterface as LocaleResolver;
use Magento\Framework\MessageQueue\DefaultValueProvider;
use Magento\Framework\MessageQueue\Envelope;
use Magento\Framework\MessageQueue\QueueRepository;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\TestFramework\MessageQueue\ClearQueueProcessor;
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

    /** @var SerializerInterface */
    private $json;

    /** @var ClearQueueProcessor */
    private $clearQueueProcessor;

    /**
     * @var QueueRepository
     */
    private $queueRepository;

    /**
     * @var DefaultValueProvider
     */
    private $defaultValueProvider;

    /** @var LocaleResolver */
    private $localeResolver;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->json = $this->_objectManager->get(SerializerInterface::class);
        $this->clearQueueProcessor = $this->_objectManager->get(ClearQueueProcessor::class);
        $this->clearQueueProcessor->execute('exportProcessor');
        $this->queueRepository = $this->_objectManager->get(QueueRepository::class);
        $this->defaultValueProvider = $this->_objectManager->get(DefaultValueProvider::class);
        $this->localeResolver = $this->_objectManager->get(LocaleResolver::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->clearQueueProcessor->execute('exportProcessor');

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
        $locale = $this->localeResolver->getLocale();
        $fileFormat = 'csv';
        $filter = ['price' => [0, 1000]];
        $this->getRequest()->setMethod(Http::METHOD_POST)
            ->setPostValue(['export_filter' => [$filter]])
            ->setParams(
                [
                    'entity' => ProductAttributeInterface::ENTITY_TYPE_CODE,
                    'file_format' => $fileFormat,
                    'fields_enclosure' => '1'
                ]
            );
        $this->dispatch('backend/admin/export/export');
        $this->assertSessionMessages($this->containsEqual($expectedSessionMessage));
        $this->assertRedirect($this->stringContains('/export/index/key/'));
        $queue = $this->queueRepository->get($this->defaultValueProvider->getConnection(), 'export');
        /** @var Envelope $message */
        $message = $queue->dequeue();
        $this->assertEquals(self::TOPIC_NAME, $message->getProperties()['topic_name']);
        $body = $this->json->unserialize($message->getBody());
        $this->assertStringContainsString(ProductAttributeInterface::ENTITY_TYPE_CODE, $body['file_name']);
        $this->assertEquals($fileFormat, $body['file_format']);
        $actualFilter = $this->json->unserialize($body['export_filter']);
        $this->assertCount(1, $actualFilter);
        $this->assertEquals($filter, reset($actualFilter));
        $this->assertNotEmpty($body['locale']);
        $this->assertEquals($locale, $body['locale']);
        $this->assertArrayHasKey('fields_enclosure', $body);
        $this->assertTrue($body['fields_enclosure']);
    }
}
