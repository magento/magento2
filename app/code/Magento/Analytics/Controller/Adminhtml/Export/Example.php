<?php
/**
 * Copyright © 2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Controller\Adminhtml\Export;
//
use Magento\Analytics\ReportXml\ReportProvider;
use Magento\Backend\App\Action;
use Magento\Framework\Filesystem;
use Magento\Framework\Archive;
use Magento\Framework\App\ResponseInterface;
use Magento\Analytics\Model\Config;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\Filesystem\DirectoryList;

class Example extends Action
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var ReportProvider
     */
    private $reportProvider;

    /**
     * @var Archive
     */
    private $archive;

    /**
     * @var Config
     */
    private $config;
    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * Example constructor.
     *
     * @param Action\Context $context
     * @param Filesystem $filesystem
     * @param ReportProvider $reportProvider
     * @param FileFactory $fileFactory
     * @param Archive $archive
     * @param Config $config
     */
    public function __construct(
        Action\Context $context,
        Filesystem $filesystem,
        ReportProvider $reportProvider,
        FileFactory $fileFactory,
        Archive $archive,
        Config $config
    ) {
        parent::__construct($context);
        $this->filesystem = $filesystem;
        $this->reportProvider = $reportProvider;
        $this->archive = $archive;
        $this->config = $config;
        $this->fileFactory = $fileFactory;
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $path = 'analytics/';
        $directory = $this->filesystem->getDirectoryWrite(DirectoryList::TMP);
        foreach($this->config->get() as $file) {
            foreach ($file['providers'] as $provider) {
                $providerObject = $this->_objectManager->get($provider[0]['class']);
                $providerObject->getReport($provider[0]['name']);
                $file = $path . $provider[0]['name'] . md5(microtime()) . '.csv';
                $directory->create('analytics/');
                $stream = $directory->openFile($file, 'w+');
                $stream->lock();
                foreach ($this->reportProvider->getReport($provider[0]['name']) as $row) {
                    $stream->writeCsv($row);
                }
                $stream->unlock();
                $stream->close();
            }
        }
        $archiveFile = $directory->getAbsolutePath(). 'analytics.tgz';
        $this->archive->pack($directory->getAbsolutePath($path), $archiveFile, true);
        $directory->delete('analytics/');
        return $this->fileFactory->create(
            $archiveFile,
            $directory->readFile('analytics.tgz'),
            DirectoryList::VAR_DIR
        );
    }
}
