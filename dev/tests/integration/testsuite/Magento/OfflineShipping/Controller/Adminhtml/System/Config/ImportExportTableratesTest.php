<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\OfflineShipping\Controller\Adminhtml\System\Config;

use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\OfflineShipping\Block\Adminhtml\Carrier\Tablerate\Grid;
use Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\Collection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Test tablerates import and export.
 *
 * @magentoAppArea adminhtml
 */
class ImportExportTableratesTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var int
     */
    private $websiteId;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->fileSystem = $this->objectManager->get(Filesystem::class);
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $this->websiteId = $this->storeManager->getWebsite()->getId();

        parent::setUp();
    }

    /**
     * Import Table Rates to be used in Configuration Settings.
     *
     * @magentoDataFixture Magento/OfflineShipping/_files/tablerate_create_file_in_tmp.php
     * @return void
     */
    public function testImportExportTablerates(): void
    {
        $importCsv = 'tablerates.csv';
        $tmpDirectory = $this->fileSystem->getDirectoryWrite(DirectoryList::SYS_TMP);
        $importCsvPath = $tmpDirectory->getAbsolutePath($importCsv);

        $_FILES['groups'] = [
            'name' => ['tablerate' => ['fields' => ['import' => ['value' => $importCsv]]]],
            'type' => ['tablerate' => ['fields' => ['import' => ['value' => 'text/csv']]]],
            'tmp_name' => ['tablerate' => ['fields' => ['import' => ['value' => $importCsvPath]]]],
            'error'=> ['tablerate' => ['fields' => ['import' => ['value' => 0]]]],
            'size' => ['tablerate' => ['fields' => ['import' => ['value' => 102]]]],
        ];

        $this->getRequest()->setPostValue(
            [
                'groups' => [
                    'tablerate' => [
                        'fields' => [
                            'condition_name' => ['value' => 'package_weight'],
                            'import' => ['value' => microtime(true)],
                        ],
                    ],
                ],
            ]
        )->setMethod(HttpRequest::METHOD_POST);

        $this->dispatch('backend/admin/system_config/save/section/carriers/website/' . $this->websiteId . '/');
        $this->assertSessionMessages(
            $this->equalTo([(string)__('You saved the configuration.')]),
            MessageInterface::TYPE_SUCCESS
        );

        /** @var Collection $tablerateCollection */
        $tablerateCollection = $this->objectManager->create(Collection::class);
        $tablerateData = $tablerateCollection->setConditionFilter('package_weight')->getItems()[0]->getData();
        $this->assertEquals('666.0000', $tablerateData['price']);
        $this->assertEquals('USA', $tablerateData['dest_country']);
        $this->assertEquals('10.0000', $tablerateData['condition_value']);

        $exportCsvContent = $this->getTablerateCsv();
        $importCsvContent = $tmpDirectory->openFile($importCsvPath, 'r')->readAll();

        $this->assertEquals($importCsvContent, $exportCsvContent);
    }

    /**
     * @return string
     */
    private function getTablerateCsv(): string
    {
        /** @var WriteInterface $varDirectory */
        $varDirectory = $this->fileSystem->getDirectoryWrite(DirectoryList::VAR_DIR);

        /** @var Grid $gridBlock */
        $gridBlock = $this->objectManager->get(LayoutInterface::class)->createBlock(Grid::class);
        $exportCsv = $gridBlock->setWebsiteId($this->websiteId)->setConditionName('package_weight')->getCsvFile();
        $exportCsvContent = $varDirectory->openFile($exportCsv['value'], 'r')->readAll();

        return $exportCsvContent;
    }
}
