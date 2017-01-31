<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model;

use Magento\Analytics\ReportXml\DB\ReportValidator;
use Magento\Framework\Filesystem\Directory\WriteInterface;

/**
 * Class ReportWriter
 *
 * @inheritdoc
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
    private $queryValidator;

    /**
     * ReportWriter constructor.
     *
     * @param Config $config
     * @param ReportValidator $queryValidator
     * @param ProviderFactory $providerFactory
     */
    public function __construct(
        Config $config,
        ReportValidator $queryValidator,
        ProviderFactory $providerFactory
    ) {
        $this->config = $config;
        $this->queryValidator = $queryValidator;
        $this->providerFactory = $providerFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function write(WriteInterface $directory, $path)
    {
        $directory->create($path);
        $errorStream = $directory->openFile($path . $this->errorsFileName, 'w+');
        foreach($this->config->get() as $file) {
            foreach ($file['providers'] as $provider) {
                $errors = $this->queryValidator->getErrors($provider[0]['name']);
                if ($errors) {
                    $errorStream->lock();
                    $errorStream->writeCsv($errors);
                    $errorStream->unlock();
                    continue;
                }

                $providerObject = $this->providerFactory->create($provider[0]['class']);
                $fileFullPath = $path . $provider[0]['name'] . md5(microtime()) . '.csv';
                $fileData = $providerObject->getReport($provider[0]['name']);

                $directory->create($path);
                $stream = $directory->openFile($fileFullPath, 'w+');
                $stream->lock();
                foreach ($fileData as $row) {
                    $stream->writeCsv($row);
                }
                $stream->unlock();
                $stream->close();
            }
        }
        if ($errorStream->tell() === 0) {
            $directory->delete($path . $this->errorsFileName);
        };
        $errorStream->close();
    }
}
