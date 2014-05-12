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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Cms\Block\Adminhtml\Wysiwyg\Images\Content;

/**
 * Uploader block for Wysiwyg Images
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Uploader extends \Magento\Backend\Block\Media\Uploader
{
    /**
     * @var \Magento\Cms\Model\Wysiwyg\Images\Storage
     */
    protected $_imagesStorage;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\File\Size $fileSize
     * @param \Magento\Cms\Model\Wysiwyg\Images\Storage $imagesStorage
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\File\Size $fileSize,
        \Magento\Cms\Model\Wysiwyg\Images\Storage $imagesStorage,
        array $data = array()
    ) {
        $this->_imagesStorage = $imagesStorage;
        parent::__construct($context, $fileSize, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $type = $this->_getMediaType();
        $allowed = $this->_imagesStorage->getAllowedExtensions($type);
        $labels = array();
        $files = array();
        foreach ($allowed as $ext) {
            $labels[] = '.' . $ext;
            $files[] = '*.' . $ext;
        }
        $this->getConfig()->setUrl(
            $this->_urlBuilder->addSessionParam()->getUrl('cms/*/upload', array('type' => $type))
        )->setFileField(
            'image'
        )->setFilters(
            array('images' => array('label' => __('Images (%1)', implode(', ', $labels)), 'files' => $files))
        );
    }

    /**
     * Return current media type based on request or data
     *
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
