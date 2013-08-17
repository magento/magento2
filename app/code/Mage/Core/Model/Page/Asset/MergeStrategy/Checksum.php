<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Merge strategy representing the following: merged file is being recreated if and only if file does not exist
 * or meta-file does not exist or checksums do not match
 */
class Mage_Core_Model_Page_Asset_MergeStrategy_Checksum implements Mage_Core_Model_Page_Asset_MergeStrategyInterface
{
    /**
     * @var Mage_Core_Model_Page_Asset_MergeStrategyInterface
     */
    private $_strategy;

    /**
     * @var Magento_Filesystem
     */
    private $_filesystem;

    /**
     * @param Mage_Core_Model_Page_Asset_MergeStrategyInterface $strategy
     * @param Magento_Filesystem $filesystem
     */
    public function __construct(
        Mage_Core_Model_Page_Asset_MergeStrategyInterface $strategy,
        Magento_Filesystem $filesystem
    ) {
        $this->_strategy = $strategy;
        $this->_filesystem = $filesystem;
    }

    /**
     * {@inheritdoc}
     */
    public function mergeFiles(array $publicFiles, $destinationFile, $contentType)
    {
        $mergedMTimeFile = $destinationFile . '.dat';

        // Check whether we have already merged these files
        $filesMTimeData = '';
        foreach ($publicFiles as $file) {
            $filesMTimeData .= $this->_filesystem->getMTime($file);
        }
        if (!($this->_filesystem->has($destinationFile) && $this->_filesystem->has($mergedMTimeFile)
            && (strcmp($filesMTimeData, $this->_filesystem->read($mergedMTimeFile)) == 0))
        ) {
            $this->_strategy->mergeFiles($publicFiles, $destinationFile, $contentType);
            $this->_filesystem->write($mergedMTimeFile, $filesMTimeData);
        }
    }
}
