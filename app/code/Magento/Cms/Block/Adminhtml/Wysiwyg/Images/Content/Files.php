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
 * @category    Magento
 * @package     Magento_Cms
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Cms\Block\Adminhtml\Wysiwyg\Images\Content;

/**
 * Directory contents block for Wysiwyg Images
 *
 * @category   Magento
 * @package    Magento_Cms
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Files extends \Magento\Backend\Block\Template
{
    /**
     * Files collection object
     *
     * @var \Magento\Data\Collection\Filesystem
     */
    protected $_filesCollection;

    /**
     * @var \Magento\Cms\Model\Wysiwyg\Images\Storage
     */
    protected $_imageStorage;

    /**
     * @var \Magento\Cms\Helper\Wysiwyg\Images
     */
    protected $_imageHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Cms\Model\Wysiwyg\Images\Storage $imageStorage
     * @param \Magento\Cms\Helper\Wysiwyg\Images $imageHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Cms\Model\Wysiwyg\Images\Storage $imageStorage,
        \Magento\Cms\Helper\Wysiwyg\Images $imageHelper,
        array $data = array()
    ) {
        $this->_imageHelper = $imageHelper;
        $this->_imageStorage = $imageStorage;
        parent::__construct($context, $data);
    }

    /**
     * Prepared Files collection for current directory
     *
     * @return \Magento\Data\Collection\Filesystem
     */
    public function getFiles()
    {
        if (!$this->_filesCollection) {
            $this->_filesCollection = $this->_imageStorage->getFilesCollection(
                $this->_imageHelper->getCurrentPath(),
                $this->_getMediaType()
            );
        }

        return $this->_filesCollection;
    }

    /**
     * Files collection count getter
     *
     * @return int
     */
    public function getFilesCount()
    {
        return $this->getFiles()->count();
    }

    /**
     * File idetifier getter
     *
     * @param  \Magento\Object $file
     * @return string
     */
    public function getFileId(\Magento\Object $file)
    {
        return $file->getId();
    }

    /**
     * File thumb URL getter
     *
     * @param  \Magento\Object $file
     * @return string
     */
    public function getFileThumbUrl(\Magento\Object $file)
    {
        return $file->getThumbUrl();
    }

    /**
     * File name URL getter
     *
     * @param  \Magento\Object $file
     * @return string
     */
    public function getFileName(\Magento\Object $file)
    {
        return $file->getName();
    }

    /**
     * Image file width getter
     *
     * @param  \Magento\Object $file
     * @return string
     */
    public function getFileWidth(\Magento\Object $file)
    {
        return $file->getWidth();
    }

    /**
     * Image file height getter
     *
     * @param  \Magento\Object $file
     * @return string
     */
    public function getFileHeight(\Magento\Object $file)
    {
        return $file->getHeight();
    }

    /**
     * File short name getter
     *
     * @param  \Magento\Object $file
     * @return string
     */
    public function getFileShortName(\Magento\Object $file)
    {
        return $file->getShortName();
    }

    /**
     * Get image width
     *
     * @return int
     */
    public function getImagesWidth()
    {
        return $this->_imageStorage->getResizeWidth();
    }

    /**
     * Get image height
     *
     * @return int
     */
    public function getImagesHeight()
    {
        return $this->_imageStorage->getResizeHeight();
    }

    /**
     * Return current media type based on request or data
     * @return string
     */
    protected function _getMediaType()
    {
        if ($this->hasData('media_type')) {
            return $this->_getData('media_type');
        }
        return $this->getRequest()->getParam('type');
    }
}
