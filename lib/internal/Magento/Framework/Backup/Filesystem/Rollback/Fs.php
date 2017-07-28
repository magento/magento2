<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Backup\Filesystem\Rollback;

use Magento\Framework\App\ObjectManager;

/**
 * Rollback worker for rolling back via local filesystem
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Fs extends AbstractRollback
{
    /**
     * @var \Magento\Framework\Backup\Filesystem\Helper
     * @since 2.2.0
     */
    private $fsHelper;

    /**
     * Files rollback implementation via local filesystem
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @see AbstractRollback::run()
     * @since 2.0.0
     */
    public function run()
    {
        $snapshotPath = $this->_snapshot->getBackupPath();

        if (!is_file($snapshotPath) || !is_readable($snapshotPath)) {
            throw new \Magento\Framework\Backup\Exception\CantLoadSnapshot(
                new \Magento\Framework\Phrase('Can\'t load snapshot archive')
            );
        }

        $fsHelper = $this->getFsHelper();

        $filesInfo = $fsHelper->getInfo(
            $this->_snapshot->getRootDir(),
            \Magento\Framework\Backup\Filesystem\Helper::INFO_WRITABLE,
            $this->_snapshot->getIgnorePaths()
        );

        if (!$filesInfo['writable']) {
            if (!empty($filesInfo['writableMeta'])) {
                throw new \Magento\Framework\Backup\Exception\NotEnoughPermissions(
                    new \Magento\Framework\Phrase(
                        'You need write permissions for: %1',
                        [implode(', ', $filesInfo['writableMeta'])]
                    )
                );
            }

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

    /**
     * @return \Magento\Framework\Backup\Filesystem\Helper
     * @deprecated 2.2.0
     * @since 2.2.0
     */
    private function getFsHelper()
    {
        if (!$this->fsHelper) {
            $this->fsHelper = ObjectManager::getInstance()->get(\Magento\Framework\Backup\Filesystem\Helper::class);
        }

        return $this->fsHelper;
    }
}
