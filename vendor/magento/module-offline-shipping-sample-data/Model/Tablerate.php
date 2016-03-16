<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OfflineShippingSampleData\Model;

use Magento\Framework\Setup\SampleData\Context as SampleDataContext;

/**
 * Class Tablerate
 */
class Tablerate
{
    /**
     * Code of "Integrity constraint violation: 1062 Duplicate entry" error
     */
    const ERROR_CODE_DUPLICATE_ENTRY = 23000;

    /**
     * @var \Magento\Framework\Setup\SampleData\FixtureManager
     */
    protected $fixtureManager;

    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csvReader;

    /**
     * @var \Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate
     */
    protected $tablerate;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Region\CollectionFactory
     */
    protected $regionCollectionFactory;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $cacheTypeList;

    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    protected $configWriter;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param SampleDataContext $sampleDataContext
     * @param \Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate $tablerate
     * @param \Magento\Framework\App\ResourceModel $resource
     * @param \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        SampleDataContext $sampleDataContext,
        \Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate $tablerate,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->fixtureManager = $sampleDataContext->getFixtureManager();
        $this->csvReader = $sampleDataContext->getCsvReader();
        $this->tablerate = $tablerate;
        $this->resource = $resource;
        $this->regionCollectionFactory = $regionCollectionFactory;
        $this->cacheTypeList = $cacheTypeList;
        $this->configWriter = $configWriter;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function install(array $fixtures)
    {
        /** @var \Magento\Framework\DB\Adapter\AdapterInterface $adapter */
        $adapter = $this->resource->getConnection('core_write');
        $regions = $this->loadDirectoryRegions();
        foreach ($fixtures as $fileName) {
            $fileName = $this->fixtureManager->getFixture($fileName);
            if (!file_exists($fileName)) {
                continue;
            }

            $rows = $this->csvReader->getData($fileName);
            $header = array_shift($rows);

            foreach ($rows as $row) {
                $data = [];
                foreach ($row as $key => $value) {
                    $data[$header[$key]] = $value;
                }
                $regionId = ($data['region'] != '*')
                    ? $regions[$data['country']][$data['region']]
                    : 0;
                try {
                    $adapter->insert(
                        $this->tablerate->getMainTable(),
                        [
                            'website_id' => $this->storeManager->getWebsite()->getId(),
                            'dest_country_id' => $data['country'],
                            'dest_region_id' => $regionId,
                            'dest_zip' => $data['zip'],
                            'condition_name' => 'package_value',
                            'condition_value' => $data['order_subtotal'],
                            'price' => $data['price'],
                            'cost' => 0,
                        ]
                    );
                } catch (\Zend_Db_Statement_Exception $e) {
                    if ($e->getCode() == self::ERROR_CODE_DUPLICATE_ENTRY) {
                        // In case if Sample data was already installed we just skip duplicated records installation
                        continue;
                    } else {
                        throw $e;
                    }
                }
            }
        }

        $this->configWriter->save('carriers/tablerate/active', 1);
        $this->configWriter->save('carriers/tablerate/condition_name', 'package_value');
        $this->cacheTypeList->cleanType('config');
    }

    /**
     * Load directory regions
     *
     * @return array
     */
    protected function loadDirectoryRegions()
    {
        $importRegions = [];
        /** @var $collection \Magento\Directory\Model\ResourceModel\Region\Collection */
        $collection = $this->regionCollectionFactory->create();
        foreach ($collection->getData() as $row) {
            $importRegions[$row['country_id']][$row['code']] = (int)$row['region_id'];
        }
        return $importRegions;
    }
}
