<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\CustomerImportExport\Model\Import;

use Magento\Customer\Api\GroupManagementInterface;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\Source\Csv;
use Magento\ImportExport\Model\Import\Source\CsvFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class CustomerDobImportTest extends TestCase
{
    /**
     * @magentoAppArea adminhtml
     */
    public function testFutureDobFailsValidation(): void
    {
        [$websiteCode, $storeCode, $groupId] = $this->getStoreContext();
        $om = Bootstrap::getObjectManager();
        /** @var Customer $model */
        $model = $om->create(Customer::class);

        $source = $this->createCsvSource(
            <<<CSV
email,_website,_store,firstname,lastname,group_id,dob
future@example.com,{$websiteCode},{$storeCode},Future,Dated,{$groupId},2099-01-01
CSV
        );

        $aggregator = $model->setParameters(['behavior' => Import::BEHAVIOR_ADD_UPDATE])
            ->setSource($source)
            ->validateData();

        $errors = $aggregator->getErrorsByCode(['invalidDob']);
        $this->assertNotEmpty($errors, 'Expected validation errors for future DOB');
    }

    /**
     * @magentoAppArea adminhtml
     */
    public function testPastDobPassesImport(): void
    {
        [$websiteCode, $storeCode, $groupId] = $this->getStoreContext();
        $om = Bootstrap::getObjectManager();
        /** @var Customer $model */
        $model = $om->create(Customer::class);

        $source = $this->createCsvSource(
            <<<CSV
email,_website,_store,firstname,lastname,group_id,dob
past@example.com,{$websiteCode},{$storeCode},Past,Dated,{$groupId},1990-01-01
CSV
        );

        $aggregator = $model->setParameters(['behavior' => Import::BEHAVIOR_ADD_UPDATE])
            ->setSource($source)
            ->validateData();

        $this->assertSame(0, $aggregator->getErrorsCount(), 'Validation should succeed for past DOB');
        $this->assertFalse($aggregator->hasToBeTerminated());

        $model->importData();
        $this->addToAssertionCount(1);
    }

    private function createCsvSource(string $csvContent): Csv
    {
        $om = Bootstrap::getObjectManager();
        /** @var Filesystem $filesystem */
        $filesystem = $om->get(Filesystem::class);
        $dirWrite = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);

        $dirWrite->create('import_dob_test');
        $relativePath = 'import_dob_test/customers_' . uniqid() . '.csv';
        $dirWrite->writeFile($relativePath, $csvContent);

        /** @var CsvFactory $csvFactory */
        $csvFactory = $om->get(CsvFactory::class);

        return $csvFactory->create([
            'file' => $dirWrite->getAbsolutePath($relativePath),
            'directory' => $dirWrite,
        ]);
    }

    private function getStoreContext(): array
    {
        $om = Bootstrap::getObjectManager();
        /** @var StoreManagerInterface $storeManager */
        $storeManager = $om->get(StoreManagerInterface::class);
        /** @var GroupManagementInterface $groupManagement */
        $groupManagement = $om->get(GroupManagementInterface::class);

        $website = $storeManager->getWebsite();
        $store = $storeManager->getDefaultStoreView();
        $groupId = $groupManagement->getDefaultGroup((int)$website->getId())->getId();

        return [$website->getCode(), $store->getCode(), $groupId];
    }
}
