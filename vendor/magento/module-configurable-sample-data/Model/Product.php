<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableSampleData\Model;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;

/**
 * Setup configurable product
 */
class Product
{
    /**
     * @var \Magento\ImportExport\Model\Import
     */
    private $importModel;

    /**
     * @var \Magento\ImportExport\Model\Import\Source\CsvFactory
     */
    private $csvSourceFactory;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadFactory
     */
    private $readFactory;

    /**
     * @var \Magento\Indexer\Model\Indexer\CollectionFactory
     */
    private $indexerCollectionFactory;

    /**
     * @var \Magento\Framework\Component\ComponentRegistrar
     */
    private $componentRegistrar;

    /**
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\ImportExport\Model\Import $importModel
     * @param \Magento\ImportExport\Model\Import\Source\CsvFactory $csvSourceFactory
     * @param \Magento\Indexer\Model\Indexer\CollectionFactory $indexerCollectionFactory
     * @param \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory
     * @param \Magento\Framework\Component\ComponentRegistrar $componentRegistrar
     */
    public function __construct(
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\ImportExport\Model\Import $importModel,
        \Magento\ImportExport\Model\Import\Source\CsvFactory $csvSourceFactory,
        \Magento\Indexer\Model\Indexer\CollectionFactory $indexerCollectionFactory,
        \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory,
        \Magento\Framework\Component\ComponentRegistrar $componentRegistrar
    ) {
        $this->eavConfig = $eavConfig;
        $this->importModel = $importModel;
        $this->csvSourceFactory = $csvSourceFactory;
        $this->indexerCollectionFactory = $indexerCollectionFactory;
        $this->readFactory = $readFactory;
        $this->componentRegistrar = $componentRegistrar;
    }

    /**
     * @inheritdoc
     */
    public function install()
    {
        $this->eavConfig->clear();
        $importModel = $this->importModel;
        $importModel->setData(
            [
                'entity' => 'catalog_product',
                'behavior' => 'append',
                'import_images_file_dir' => 'pub/media/catalog/product',
                Import::FIELD_NAME_VALIDATION_STRATEGY =>
                    ProcessingErrorAggregatorInterface::VALIDATION_STRATEGY_SKIP_ERRORS
            ]
        );

        $source = $this->csvSourceFactory->create(
            [
                'file' => 'fixtures/products.csv',
                'directory' => $this->readFactory->create(
                    $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, 'Magento_ConfigurableSampleData')
                )
            ]
        );

        $currentPath = getcwd();
        chdir(BP);
        $importModel->validateSource($source);
        $importModel->importSource();

        chdir($currentPath);

        $this->eavConfig->clear();
        $this->reindex();
    }

    /**
     * Perform full reindex
     */
    private function reindex()
    {
        foreach ($this->indexerCollectionFactory->create()->getItems() as $indexer) {
            $indexer->reindexAll();
        }
    }
}
