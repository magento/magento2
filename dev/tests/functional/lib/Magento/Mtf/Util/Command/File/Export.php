<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Util\Command\File;

use Magento\Mtf\ObjectManagerInterface;
use Magento\Mtf\Util\Command\File\Resource\Data;
use Magento\Mtf\Util\Command\File\Resource\ReaderInterface;

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
    private $readerPath = 'Magento\Mtf\Util\Command\File\Resource\%sReader';

    /**
     * Object manager instance.
     *
     * @var ObjectManagerInterface $objectManager
     */
    private $objectManager;

    /**
     * File reader for Magento export files.
     *
     * @var ReaderInterface
     */
    private $reader;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param string $type [optional]
     */
    public function __construct(ObjectManagerInterface $objectManager, $type = 'product')
    {
        $this->objectManager = $objectManager;
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
        try {
            $readerPath = sprintf($this->readerPath, ucfirst($type));
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
     */
    public function getByName($name)
    {
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
     */
    public function getLatest()
    {
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
     */
    public function getByDateRange($start, $end)
    {
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
     */
    public function getAll()
    {
        return $this->reader->getData();
    }
}
