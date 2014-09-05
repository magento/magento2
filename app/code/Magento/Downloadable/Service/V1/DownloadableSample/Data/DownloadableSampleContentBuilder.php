<?php
/**
 * Downloadable Link Builder
 *
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Downloadable\Service\V1\DownloadableSample\Data;

use \Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder;
use \Magento\Downloadable\Service\V1\Data\FileContent;

/**
 * @codeCoverageIgnore
 */
class DownloadableSampleContentBuilder extends AbstractExtensibleObjectBuilder
{
    /**
     * Set link title
     *
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        return $this->_set(DownloadableSampleContent::TITLE, $title);
    }

    /**
     * Set sample file content
     *
     * @param FileContent $sampleFile
     * @return $this
     */
    public function setSampleFile($sampleFile)
    {
        return $this->_set(DownloadableSampleContent::SAMPLE_FILE, $sampleFile);
    }

    /**
     * Set sample type ('url' or 'file')
     *
     * @param string $sampleType
     * @return $this
     */
    public function setSampleType($sampleType)
    {
        return $this->_set(DownloadableSampleContent::SAMPLE_TYPE, $sampleType);
    }

    /**
     * Set sample URL
     *
     * @param string $sampleUrl
     * @return $this
     */
    public function setSampleUrl($sampleUrl)
    {
        return $this->_set(DownloadableSampleContent::SAMPLE_URL, $sampleUrl);
    }

    /**
     * Set sample sort order
     *
     * @param int $sortOrder
     * @return $this
     */
    public function setSortOrder($sortOrder)
    {
        return $this->_set(DownloadableSampleContent::SORT_ORDER, $sortOrder);
    }
}
