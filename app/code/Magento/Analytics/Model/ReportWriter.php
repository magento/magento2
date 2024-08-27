<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Analytics\Model;

use Magento\Analytics\ReportXml\DB\ReportValidator;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\File\WriteInterface as FileWriteInterface;

/**
 * Writes reports in files in csv format
 */
class ReportWriter implements ReportWriterInterface
{
    /**
     * File name for error reporting file in archive
     *
     * @var string
     */
    private $errorsFileName = 'errors.csv';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var ProviderFactory
     */
    private $providerFactory;

    /**
     * @var ReportValidator
     */
    private $reportValidator;

    /**
     * ReportWriter constructor.
     *
     * @param ConfigInterface $config
     * @param ReportValidator $reportValidator
     * @param ProviderFactory $providerFactory
     */
    public function __construct(
        ConfigInterface $config,
        ReportValidator $reportValidator,
        ProviderFactory $providerFactory
    ) {
        $this->config = $config;
        $this->reportValidator = $reportValidator;
        $this->providerFactory = $providerFactory;
    }

    /**
     * @inheritdoc
     */
    public function write(WriteInterface $directory, $path)
    {
        $errorsList = [];
        foreach ($this->config->get() as $file) {
            $provider = reset($file['providers']);
            if (isset($provider['parameters']['name'])) {
                $error = $this->reportValidator->validate($provider['parameters']['name']);
                if ($error) {
                    $errorsList[] = $error;
                    continue;
                }
            }
            $this->prepareData($provider, $directory, $path);
        }
        if ($errorsList) {
            $errorStream = $directory->openFile($path . $this->errorsFileName, 'w+');
            foreach ($errorsList as $error) {
                $errorStream->lock();
                $errorStream->writeCsv($error);
                $errorStream->unlock();
            }
            $errorStream->close();
        }

        return true;
    }

    /**
     * Prepare report data
     *
     * @param array $provider
     * @param WriteInterface $directory
     * @param string $path
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function prepareData(array $provider, WriteInterface $directory, string $path)
    {
        /** @var  $providerObject */
        $providerObject = $this->providerFactory->create($provider['class']);
        $fileName = $provider['parameters'] ? $provider['parameters']['name'] : $provider['name'];
        $fileFullPath = $path . $fileName . '.csv';

        $stream = $directory->openFile($fileFullPath, 'w+');
        $stream->lock();

        $headers = [];
        if ($providerObject instanceof \Magento\Analytics\ReportXml\BatchReportProviderInterface) {
            $fileData = $providerObject->getBatchReport(...array_values($provider['parameters']));
            do {
                $this->doWrite($fileData, $stream, $headers);
                $fileData = $providerObject->getBatchReport(...array_values($provider['parameters']));
                $fileData->rewind();
            } while ($fileData->valid());
        } else {
            $fileData = $providerObject->getReport(...array_values($provider['parameters']));
            $this->doWrite($fileData, $stream, $headers);
        }

        $stream->unlock();
        $stream->close();
    }

    /**
     * Write data to file
     *
     * @param \Traversable $fileData
     * @param FileWriteInterface $stream
     * @param array $headers
     * @return void
     */
    private function doWrite(\Traversable $fileData, FileWriteInterface $stream, array $headers)
    {
        foreach ($fileData as $row) {
            if (!$headers) {
                $headers = array_keys($row);
                $stream->writeCsv($headers);
            }
            $stream->writeCsv($this->prepareRow($row));
        }
    }

    /**
     * Replace wrong symbols in row
     *
     * Strip backslashes before double quotes so they will be properly escaped in the generated csv
     *
     * @see fputcsv()
     * @param array $row
     * @return array
     */
    private function prepareRow(array $row): array
    {
        return preg_replace('/\\\+(?=\")/', '', $row);
    }
}
