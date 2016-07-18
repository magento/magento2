<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\File\ReadInterface;
use Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\CSV\ColumnResolver;
use Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\CSV\ColumnResolverFactory;
use Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\CSV\RowException;
use Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\CSV\RowParser;
use Magento\Store\Model\StoreManagerInterface;

class Import
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var ScopeConfigInterface
     */
    private $coreConfig;

    /**
     * @var array
     */
    private $errors = [];

    /**
     * @var CSV\RowParser
     */
    private $rowParser;

    /**
     * @var CSV\ColumnResolverFactory
     */
    private $columnResolverFactory;

    /**
     * @var DataHashGenerator
     */
    private $dataHashGenerator;

    /**
     * @var array
     */
    private $uniqueHash = [];

    /**
     * Import constructor.
     * @param StoreManagerInterface $storeManager
     * @param Filesystem $filesystem
     * @param ScopeConfigInterface $coreConfig
     * @param CSV\RowParser $rowParser
     * @param CSV\ColumnResolverFactory $columnResolverFactory
     * @param DataHashGenerator $dataHashGenerator
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Filesystem $filesystem,
        ScopeConfigInterface $coreConfig,
        RowParser $rowParser,
        ColumnResolverFactory $columnResolverFactory,
        DataHashGenerator $dataHashGenerator
    ) {
        $this->storeManager = $storeManager;
        $this->filesystem = $filesystem;
        $this->coreConfig = $coreConfig;
        $this->rowParser = $rowParser;
        $this->columnResolverFactory = $columnResolverFactory;
        $this->dataHashGenerator = $dataHashGenerator;
    }

    /**
     * @return bool
     */
    public function hasErrors()
    {
        return (bool)count($this->getErrors());
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return $this->rowParser->getColumns();
    }

    /**
     * @param ReadInterface $file
     * @param int $websiteId
     * @param string $conditionShortName
     * @param string $conditionFullName
     * @param int $bunchSize
     * @return \Generator
     * @throws LocalizedException
     */
    public function getData(ReadInterface $file, $websiteId, $conditionShortName, $conditionFullName, $bunchSize = 5000)
    {
        $this->errors = [];

        $headers = $this->getHeaders($file);
        /** @var ColumnResolver $columnResolver */
        $columnResolver = $this->columnResolverFactory->create(['headers' => $headers]);

        $rowNumber = 1;
        $items = [];
        while (false !== ($csvLine = $file->readCsv())) {
            try {
                $rowNumber++;
                if (empty($csvLine)) {
                    continue;
                }
                $rowData = $this->rowParser->parse(
                    $csvLine,
                    $rowNumber,
                    $websiteId,
                    $conditionShortName,
                    $conditionFullName,
                    $columnResolver
                );

                // protect from duplicate
                $hash = $this->dataHashGenerator->getHash($rowData);
                if (array_key_exists($hash, $this->uniqueHash)) {
                    throw new RowException(
                        __(
                            'Duplicate Row #%1 (duplicates row #%2)',
                            $rowNumber,
                            $this->uniqueHash[$hash]
                        )
                    );
                }
                $this->uniqueHash[$hash] = $rowNumber;

                $items[] = $rowData;
                if (count($items) === $bunchSize) {
                    yield $items;
                    $items = [];
                }
            } catch (RowException $e) {
                $this->errors[] = $e->getMessage();
            }
        }
        if (count($items)) {
            yield $items;
        }
    }

    /**
     * @param ReadInterface $file
     * @return array|bool
     * @throws LocalizedException
     */
    private function getHeaders(ReadInterface $file)
    {
        // check and skip headers
        $headers = $file->readCsv();
        if ($headers === false || count($headers) < 5) {
            throw new LocalizedException(__('Please correct Table Rates File Format.'));
        }
        return $headers;
    }
}
