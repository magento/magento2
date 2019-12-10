<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Util\Command\File;

use Magento\Mtf\ObjectManagerInterface;
use Magento\Mtf\Util\Command\File\Export\Data;
use Magento\Mtf\Util\Command\File\Export\ReaderInterface;
use Magento\ImportExport\Test\Page\Adminhtml\AdminExportIndex;

/**
 * Get Exporting file from the Magento.
 */
class Export implements ExportInterface
{
    /**
     * Path to the Reader.
     *
     * @var string
     */
    private $readerPath = 'Magento\Mtf\Util\Command\File\Export\%sReader';

    /**
     * Object manager instance.
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * File reader for Magento export files.
     *
     * @var ReaderInterface
     */
    private $reader;

    /**
     * Admin export index page.
     *
     * @var AdminExportIndex
     */
    private $adminExportIndex;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param AdminExportIndex $adminExportIndex
     * @param string $type
     * @throws \ReflectionException
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        AdminExportIndex $adminExportIndex,
        $type = 'product'
    ) {
        $this->objectManager = $objectManager;
        $this->adminExportIndex = $adminExportIndex;
        $this->reader = $this->getReader($type);
    }

    /**
     * Get reader for export files.
     *
     * @param string $type
     * @return ReaderInterface
     * @throws \ReflectionException
     */
    private function getReader($type)
    {
        $readerPath = sprintf($this->readerPath, ucfirst($type));
        try {
            return $this->objectManager->create($readerPath);
        } catch (\ReflectionException $e) {
            throw new \ReflectionException("Virtual type '$readerPath' does not exist. Please, check it in di.xml.");
        }
    }

    /**
     * Get the export file by name.
     *
     * @param string $name
     * @return Data|null
     * @throws \Exception
     */
    public function getByName($name)
    {
        $this->downloadFile();
        $this->reader->getData();
        foreach ($this->reader->getData() as $file) {
            if ($file->getName() === $name) {
                return $file;
            }
        }

        return null;
    }

    /**
     * Get latest created the export file.
     *
     * @return Data|null
     * @throws \Exception
     */
    public function getLatest()
    {
        $this->downloadFile();
        $max = 0;
        $latest = null;
        foreach ($this->reader->getData() as $file) {
            if ($file->getDate() > $max) {
                $max = $file->getDate();
                $latest = $file;
            }
        }

        return $latest;
    }

    /**
     * Get all export files by date range using unix time stamp.
     *
     * @param string $start
     * @param string $end
     * @return Data[]
     * @throws \Exception
     */
    public function getByDateRange($start, $end)
    {
        $this->downloadFile();
        $files = [];
        foreach ($this->reader->getData() as $file) {
            if ($file->getDate() > $start && $file->getDate() < $end) {
                $files[] = $file;
            }
        }

        return $files;
    }

    /**
     * Get all export files.
     *
     * @return Data[]
     * @throws \Exception
     */
    public function getAll()
    {
        $this->downloadFile();
        return $this->reader->getData();
    }

    /**
     * Download exported file
     *
     * @return void
     * @throws \Exception
     */
    private function downloadFile()
    {
        $this->adminExportIndex->open();
        /** @var \Magento\ImportExport\Test\Block\Adminhtml\Export\ExportedGrid $exportedGrid */
        $exportedGrid = $this->adminExportIndex->getExportedGrid();
        $exportedGrid->downloadFirstFile();
    }
}
