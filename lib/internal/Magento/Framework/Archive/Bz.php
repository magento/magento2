<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Class to work with bzip2 archives
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Framework\Archive;

class Bz extends \Magento\Framework\Archive\AbstractArchive implements \Magento\Framework\Archive\ArchiveInterface
{
    /**
     * Pack file by BZIP2 compressor.
     *
     * @param string $source
     * @param string $destination
     * @return string
     */
    public function pack($source, $destination)
    {
        $fileReader = new \Magento\Framework\Archive\Helper\File($source);
        $fileReader->open('r');

        $archiveWriter = new \Magento\Framework\Archive\Helper\File\Bz($destination);
        $archiveWriter->open('w');

        while (!$fileReader->eof()) {
            $archiveWriter->write($fileReader->read());
        }

        $fileReader->close();
        $archiveWriter->close();

        return $destination;
    }

    /**
     * Unpack file by BZIP2 compressor.
     *
     * @param string $source
     * @param string $destination
     * @return string
     */
    public function unpack($source, $destination)
    {
        if (is_dir($destination)) {
            $file = $this->getFilename($source);
            $destination = $destination . $file;
        }

        $archiveReader = new \Magento\Framework\Archive\Helper\File\Bz($source);
        $archiveReader->open('r');

        $fileWriter = new \Magento\Framework\Archive\Helper\File($destination);
        $fileWriter->open('w');

        while (!$archiveReader->eof()) {
            $fileWriter->write($archiveReader->read());
        }

        return $destination;
    }
}
