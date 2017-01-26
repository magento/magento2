<?php
/**
 * Copyright © 2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Model;

use Magento\Analytics\ReportXml\ReportProvider;
use Magento\Framework\Filesystem;
use Magento\Framework\Archive;
use Magento\Analytics\Model\Config;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class Export
 *
 * Executes export of collected data
 * Iterates registered providers @see etc/analytics.xml
 * Collects data (to TMP folder) and packs them into archive
 * Returns archive content
 */
class Export
{
    /**
     * Path for output files
     *
     * @var string
     */
    private $path = 'analytics/';

    /**
     * Name of temp archive
     *
     * @var string
     */
    private $archiveName = 'analytics.tgz';

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var ProviderFactory
     */
    private $providerFactory;

    /**
     * @var ReportProvider
     */
    private $reportProvider;

    /**
     * @var Archive
     */
    private $archive;

    /**
     * @var \Magento\Analytics\Model\Config
     */
    private $config;

    /**
     * Export constructor.
     *
     * @param Filesystem $filesystem
     * @param ProviderFactory $providerFactory
     * @param ReportProvider $reportProvider
     * @param Archive $archive
     * @param \Magento\Analytics\Model\Config $config
     */
    public function __construct(
        Filesystem $filesystem,
        ProviderFactory $providerFactory,
        ReportProvider $reportProvider,
        Archive $archive,
        Config $config
    ) {
        $this->filesystem = $filesystem;
        $this->providerFactory = $providerFactory;
        $this->reportProvider = $reportProvider;
        $this->archive = $archive;
        $this->config = $config;
    }

    /**
     * Returns archive content
     *
     * @return string
     */
    public function getArchiveContent()
    {
        $directory = $this->filesystem->getDirectoryWrite(DirectoryList::TMP);
        foreach($this->config->get() as $file) {
            foreach ($file['providers'] as $provider) {
                $providerObject = $this->providerFactory->create($provider[0]['class']);
                $providerObject->getReport($provider[0]['name']);
                $file = $this->path . $provider[0]['name'] . md5(microtime()) . '.csv';
                $directory->create($this->path);
                $stream = $directory->openFile($file, 'w+');
                $stream->lock();
                foreach ($this->reportProvider->getReport($provider[0]['name']) as $row) {
                    $stream->writeCsv($row);
                }
                $stream->unlock();
                $stream->close();
            }
        }
        $archiveFile = $directory->getAbsolutePath(). $this->archiveName;
        $this->archive->pack($directory->getAbsolutePath($this->path), $archiveFile, true);
        $directory->delete($this->path);
        return $directory->readFile($this->archiveName);
    }
}
