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

    public function __construct(
        Action\Context $context,
        Filesystem $filesystem,
        ReportProvider $reportProvider,
        Archive $archive,
        Config $config
    ) {
        parent::__construct($context);
        $this->filesystem = $filesystem;
        $this->reportProvider = $reportProvider;
        $this->archive = $archive;
        $this->config = $config;
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
        $directory = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR);
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
//        $archiveFile = $directory->getAbsolutePath($path). 'analytics.tgz';
//        $this->archive->pack($directory->getAbsolutePath($path), $archiveFile, false);
    }
}
