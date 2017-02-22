<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Backup;


/**
 * Class to work media folder and database backups
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Media extends Snapshot
{
    /**
     * Implementation Rollback functionality for Media
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return bool
     */
    public function rollback()
    {
        $this->_prepareIgnoreList();
        return parent::rollback();
    }

    /**
     * Implementation Create Backup functionality for Media
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return bool
     */
    public function create()
    {
        $this->_prepareIgnoreList();
        return parent::create();
    }

    /**
     * Overlap getType
     *
     * @return string
     * @see BackupInterface::getType()
     */
    public function getType()
    {
        return 'media';
    }

    /**
     * Add all folders and files except media and db backup to ignore list
     *
     * @return $this
     */
    protected function _prepareIgnoreList()
    {
        $rootDir = $this->getRootDir();
        $map = [
            $rootDir => ['var', 'pub'],
            $rootDir . '/pub' => ['media'],
            $rootDir . '/var' => [$this->getDbBackupFilename()],
        ];

        foreach ($map as $path => $whiteList) {
            foreach (new \DirectoryIterator($path) as $item) {
                $filename = $item->getFilename();
                if (!$item->isDot() && !in_array($filename, $whiteList)) {
                    $this->addIgnorePaths(str_replace('\\', '/', $item->getPathname()));
                }
            }
        }

        return $this;
    }
}
