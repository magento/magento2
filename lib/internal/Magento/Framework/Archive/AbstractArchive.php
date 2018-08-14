<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Class to work with archives
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Framework\Archive;

class AbstractArchive
{
    /**
     * Write data to file. If file can't be opened - throw exception
     *
     * @param string $destination
     * @param string $data
     * @return true
     * @throws \Exception
     */
    protected function _writeFile($destination, $data)
    {
        $destination = trim($destination);
        if (false === file_put_contents($destination, $data)) {
            throw new \Exception("Can't write to file: " . $destination);
        }
        return true;
    }

    /**
     * Read data from file. If file can't be opened, throw to exception.
     *
     * @param string $source
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _readFile($source)
    {
        $data = '';
        if (is_file($source) && is_readable($source)) {
            $data = @file_get_contents($source);
            if ($data === false) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    new \Magento\Framework\Phrase("Can't get contents from: %1", [$source])
                );
            }
        }
        return $data;
    }

    /**
     * Get file name from source (URI) without last extension.
     *
     * @param string $source
     * @param bool $withExtension
     * @return string
     */
    public function getFilename($source, $withExtension = false)
    {
        $file = str_replace(dirname($source) . '/', '', $source);
        if (!$withExtension) {
            $file = substr($file, 0, strrpos($file, '.'));
        }
        return $file;
    }
}
