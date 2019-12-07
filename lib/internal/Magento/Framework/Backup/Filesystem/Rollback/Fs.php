<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Backup\Filesystem\Rollback;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Archive;
use Magento\Framework\Archive\Gz;
use Magento\Framework\Archive\Helper\File;
use Magento\Framework\Archive\Helper\File\Gz as HelperGz;
use Magento\Framework\Archive\Tar;
use Magento\Framework\Backup\Exception\CantLoadSnapshot;
use Magento\Framework\Backup\Exception\NotEnoughPermissions;
use Magento\Framework\Backup\Filesystem\Helper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

/**
 * Rollback worker for rolling back via local filesystem
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Fs extends AbstractRollback
{
    /**
     * @var Helper
     */
    private $fsHelper;

    /**
     * Files rollback implementation via local filesystem
     *
     * @return void
     * @throws LocalizedException
     *
     * @see AbstractRollback::run()
     */
    public function run()
    {
        $snapshotPath = $this->_snapshot->getBackupPath();

        if (!is_file($snapshotPath) || !is_readable($snapshotPath)) {
            throw new CantLoadSnapshot(
                new Phrase('Can\'t load snapshot archive')
            );
        }

        $fsHelper = $this->getFsHelper();

        $filesInfo = $fsHelper->getInfo(
            $this->_snapshot->getRootDir(),
            Helper::INFO_WRITABLE,
            $this->_snapshot->getIgnorePaths()
        );

        if (!$filesInfo['writable']) {
            if (!empty($filesInfo['writableMeta'])) {
                throw new NotEnoughPermissions(
                    new Phrase(
                        'You need write permissions for: %1',
                        [implode(', ', $filesInfo['writableMeta'])]
                    )
                );
            }

            throw new NotEnoughPermissions(
                new Phrase("The rollback can't be executed because not all files are writable.")
            );
        }

        $archiver = new Archive();

        /**
         * we need these fake initializations because all magento's files in filesystem will be deleted and autoloader
         * won't be able to load classes that we need for unpacking
         */
        new Tar();
        new Gz();
        new File('');
        new HelperGz('');
        new LocalizedException(new Phrase('dummy'));

        if (!$this->_snapshot->keepSourceFile()) {
            $fsHelper->rm($this->_snapshot->getRootDir(), $this->_snapshot->getIgnorePaths());
        }
        $archiver->unpack($snapshotPath, $this->_snapshot->getRootDir());

        if ($this->_snapshot->keepSourceFile() === false) {
            @unlink($snapshotPath);
        }
    }

    /**
     * Get file system helper instance
     *
     * @return Helper
     * @deprecated 100.2.0
     */
    private function getFsHelper()
    {
        if (!$this->fsHelper) {
            $this->fsHelper = ObjectManager::getInstance()->get(Helper::class);
        }

        return $this->fsHelper;
    }
}
