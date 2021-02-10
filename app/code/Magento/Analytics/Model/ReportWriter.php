<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Analytics\Model;

use Magento\Analytics\ReportXml\DB\ReportValidator;
use Magento\Framework\Filesystem\Directory\WriteInterface;

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
            /** @var  $providerObject */
            $providerObject = $this->providerFactory->create($provider['class']);
            $fileName = $provider['parameters'] ? $provider['parameters']['name'] : $provider['name'];
            $fileFullPath = $path . $fileName . '.csv';
            $fileData = $providerObject->getReport(...array_values($provider['parameters']));
            $stream = $directory->openFile($fileFullPath, 'w+');
            $stream->lock();
            $headers = [];
            foreach ($fileData as $row) {
                if (!$headers) {
                    $headers = array_keys($row);
                    $stream->writeCsv($headers);
                }
                $stream->writeCsv($this->prepareRow($row));
            }
            $stream->unlock();
            $stream->close();
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
     * Replace wrong symbols in row
     *
     * @param array $row
     * @return array
     */
    private function prepareRow(array $row): array
    {
        $row = preg_replace('/(?<!\\\\)"/', '\\"', $row);
        $row = preg_replace('/[\\\\]+/', '\\', $row);

        return $row;
    }
}
