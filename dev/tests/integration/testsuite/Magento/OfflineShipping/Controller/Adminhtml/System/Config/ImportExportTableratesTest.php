<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\OfflineShipping\Controller\Adminhtml\System\Config;

use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\OfflineShipping\Block\Adminhtml\Carrier\Tablerate\Grid;
use Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate;
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
     * @var DirectoryList
     */
    private $varDirectory;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->fileSystem = $this->objectManager->get(Filesystem::class);
        $this->varDirectory = $this->fileSystem->getDirectoryWrite(DirectoryList::VAR_DIR);

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

        $this->dispatch('backend/admin/system_config/save/section/carriers/website/1/');
        $this->assertSessionMessages(
            $this->equalTo([(string)__('You saved the configuration.')]),
            MessageInterface::TYPE_SUCCESS
        );

        $tablerateResourceModel = $this->objectManager->create(Tablerate::class);
        $connection = $tablerateResourceModel->getConnection();

        $selectData = $connection->select()->from($tablerateResourceModel->getTable('shipping_tablerate'));
        $this->assertNotEmpty($connection->fetchRow($selectData));

        $exportCsv = $this->getTablerateCsv();
        $exportCsvContent = $this->varDirectory->openFile($exportCsv['value'], 'r')->readAll();
        $importCsvContent = $tmpDirectory->openFile($importCsvPath, 'r')->readAll();

        $this->assertEquals($importCsvContent, $exportCsvContent);
    }

    /**
     * @return array
     */
    private function getTablerateCsv(): array
    {
        /** @var Grid $gridBlock */
        $gridBlock = $this->objectManager->get(LayoutInterface::class)->createBlock(Grid::class);
        $exportCsv = $gridBlock->setWebsiteId(1)->setConditionName('package_weight')->getCsvFile();

        return $exportCsv;
    }
}
