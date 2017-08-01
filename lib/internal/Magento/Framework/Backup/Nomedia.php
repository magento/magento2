<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Backup;

/**
 * Class to work system backup that excludes media folder
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Nomedia extends \Magento\Framework\Backup\Media
{
    /**
     * Overlap getType
     *
     * @return string
     * @see BackupInterface::getType()
     * @since 2.0.0
     */
    public function getType()
    {
        return 'nomedia';
    }

    /**
     * Add media folder to ignore list
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _prepareIgnoreList()
    {
        $rootDir = $this->getRootDir();
        $this->addIgnorePaths([$rootDir . '/media', $rootDir . '/pub/media']);
        return $this;
    }
}
