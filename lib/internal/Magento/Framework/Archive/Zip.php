<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Archive;

/**
 * Zip compressed file archive.
 */
class Zip extends AbstractArchive implements ArchiveInterface
{
    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct()
    {
        $type = 'Zip';
        if (!class_exists('\ZipArchive')) {
            throw new \Magento\Framework\Exception\LocalizedException(
                new \Magento\Framework\Phrase('\'%1\' file extension is not supported', [$type])
            );
        }
    }

    /**
     * Pack file.
     *
     * @param string $source
     * @param string $destination
     *
     * @return string
     */
    public function pack($source, $destination)
    {
        $zip = new \ZipArchive();
        $zip->open($destination, \ZipArchive::CREATE);
        $zip->addFile($source);
        $zip->close();
        return $destination;
    }

    /**
     * Unpack file.
     *
     * @param string $source
     * @param string $destination
     *
     * @return string
     */
    public function unpack($source, $destination)
    {
        $zip = new \ZipArchive();
        if ($zip->open($source) === true) {
            $zip->renameIndex(0, basename($destination));
            $filename = $zip->getNameIndex(0) ?: '';
            if ($filename) {
                $zip->extractTo(dirname($destination), $filename);
            } else {
                $destination = '';
            }
            $zip->close();
        } else {
            $destination = '';
        }

        return $destination;
    }
}
