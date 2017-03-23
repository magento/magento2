<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Class to work with zip archives
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Framework\Archive;

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
        $zip->open($source);
        $filename = $zip->getNameIndex(0);
        $zip->extractTo(dirname($destination), $filename);
        rename(dirname($destination).'/'.$filename, $destination);
        $zip->close();
        return $destination;
    }
}
