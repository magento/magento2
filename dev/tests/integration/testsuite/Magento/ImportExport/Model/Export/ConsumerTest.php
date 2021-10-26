<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Model\Export;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\MessageQueue\MessageEncoder;
use Magento\Framework\ObjectManagerInterface;
use Magento\MysqlMq\Model\Driver\Queue;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for export consumer
 *
 * @see \Magento\ImportExport\Model\Export\Consumer
 *
 * @magentoDbIsolation enabled
 * @magentoAppArea adminhtml
 */
class ConsumerTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var MessageEncoder */
    private $messageEncoder;

    /** @var Consumer */
    private $consumer;

    /** @var Queue */
    private $queue;

    /** @var WriteInterface */
    private $directory;

    /** @var string */
    private $filePath;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->queue = $this->objectManager->create(Queue::class, ['queueName' => 'export']);
        $this->messageEncoder = $this->objectManager->get(MessageEncoder::class);
        $this->consumer = $this->objectManager->get(Consumer::class);
        $filesystem = $this->objectManager->get(Filesystem::class);
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_IMPORT_EXPORT);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        if ($this->filePath && $this->directory->isExist($this->filePath)) {
            $this->directory->delete($this->filePath);
        }

        parent::tearDown();
    }

    /**
     * @magentoConfigFixture default_store admin/security/use_form_key 1
     *
     * @magentoDataFixture Magento/ImportExport/_files/export_queue_data.php
     * @magentoDataFixture Magento/Catalog/_files/product_virtual.php
     *
     * @return void
     */
    public function testProcess(): void
    {
        $envelope = $this->queue->dequeue();
        $decodedMessage = $this->messageEncoder->decode('import_export.export', $envelope->getBody());
        $this->consumer->process($decodedMessage);
        $this->filePath = 'export/' . $decodedMessage->getFileName();
        $this->assertTrue($this->directory->isExist($this->filePath));
        $data = $this->getCsvData($this->directory->getAbsolutePath($this->filePath));
        $this->assertCount(2, $data);
        $skuPosition = $this->getSkuPosition($data);
        $this->assertNotNull($skuPosition);
        $this->assertEquals('simple2', $data[1][$skuPosition]);
    }

    /**
     * Get sku position from array.
     *
     * @param array $csvFileData
     *
     * @return int|null
     */
    private function getSkuPosition(array $csvFileData): ?int
    {
        foreach ($csvFileData as $data) {
            $skuPosition = array_search(ProductInterface::SKU, $data);

            if ($skuPosition !== false) {
                return $skuPosition;
            }
        }

        return null;
    }

    /**
     * Parse csv file and return csv data as array
     *
     * @param string $filePath
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function getCsvData(string $filePath): array
    {
        $driver = $this->directory->getDriver();
        $fileResource = $driver->fileOpen($filePath, 'r');

        $data = [];
        while ($rowData = $driver->fileGetCsv($fileResource, 100000)) {
            $data[] = $rowData;
        }
        $driver->fileClose($fileResource);

        return $data;
    }
}
