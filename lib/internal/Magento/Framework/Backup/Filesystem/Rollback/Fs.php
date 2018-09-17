<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Backup\Filesystem\Rollback;

/**
 * Rollback worker for rolling back via local filesystem
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Fs extends AbstractRollback
{
    /**
     * Files rollback implementation via local filesystem
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @see AbstractRollback::run()
     */
    public function run()
    {
        $snapshotPath = $this->_snapshot->getBackupPath();

        if (!is_file($snapshotPath) || !is_readable($snapshotPath)) {
            throw new \Magento\Framework\Backup\Exception\CantLoadSnapshot(
                new \Magento\Framework\Phrase('Can\'t load snapshot archive')
            );
        }

        $fsHelper = new \Magento\Framework\Backup\Filesystem\Helper();

        $filesInfo = $fsHelper->getInfo(
            $this->_snapshot->getRootDir(),
            \Magento\Framework\Backup\Filesystem\Helper::INFO_WRITABLE,
            $this->_snapshot->getIgnorePaths()
        );

        if (!$filesInfo['writable']) {
            throw new \Magento\Framework\Backup\Exception\NotEnoughPermissions(
                new \Magento\Framework\Phrase('Unable to make rollback because not all files are writable')
            );
        }

        $archiver = new \Magento\Framework\Archive();

        /**
         * we need these fake initializations because all magento's files in filesystem will be deleted and autoloader
         * wont be able to load classes that we need for unpacking
         */
        new \Magento\Framework\Archive\Tar();
        new \Magento\Framework\Archive\Gz();
        new \Magento\Framework\Archive\Helper\File('');
        new \Magento\Framework\Archive\Helper\File\Gz('');
        new \Magento\Framework\Exception\LocalizedException(new \Magento\Framework\Phrase('dummy'));

        $fsHelper->rm($this->_snapshot->getRootDir(), $this->_snapshot->getIgnorePaths());
        $archiver->unpack($snapshotPath, $this->_snapshot->getRootDir());
    }
}
