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
namespace Magento\Backend\Block\Media;

/**
 * Adminhtml media library uploader
 */
class Uploader extends \Magento\Backend\Block\Widget
{
    /**
     * @var \Magento\Framework\Object
     */
    protected $_config;

    /**
     * @var string
     */
    protected $_template = 'Magento_Backend::media/uploader.phtml';

    /**
     * @var \Magento\Framework\File\Size
     */
    protected $_fileSizeService;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\File\Size $fileSize
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\File\Size $fileSize,
        array $data = array()
    ) {
        $this->_fileSizeService = $fileSize;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setId($this->getId() . '_Uploader');

        $uploadUrl = $this->_urlBuilder->addSessionParam()->getUrl('adminhtml/*/upload');
        $this->getConfig()->setUrl($uploadUrl);
        $this->getConfig()->setParams(array('form_key' => $this->getFormKey()));
        $this->getConfig()->setFileField('file');
        $this->getConfig()->setFilters(
            array(
                'images' => array(
                    'label' => __('Images (.gif, .jpg, .png)'),
                    'files' => array('*.gif', '*.jpg', '*.png')
                ),
                'media' => array(
                    'label' => __('Media (.avi, .flv, .swf)'),
                    'files' => array('*.avi', '*.flv', '*.swf')
                ),
                'all' => array('label' => __('All Files'), 'files' => array('*.*'))
            )
        );
    }

    /**
     * Get file size
     *
     * @return \Magento\Framework\File\Size
     */
    public function getFileSizeService()
    {
        return $this->_fileSizeService;
    }

    /**
     * Prepares layout and set element renderer
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->addPageAsset('jquery/fileUploader/css/jquery.fileupload-ui.css');
        return parent::_prepareLayout();
    }

    /**
     * Retrieve uploader js object name
     *
     * @return string
     */
    public function getJsObjectName()
    {
        return $this->getHtmlId() . 'JsObject';
    }

    /**
     * Retrieve config json
     *
     * @return string
     */
    public function getConfigJson()
    {
        return $this->_coreData->jsonEncode($this->getConfig()->getData());
    }

    /**
     * Retrieve config object
     *
     * @return \Magento\Framework\Object
     */
    public function getConfig()
    {
        if (null === $this->_config) {
            $this->_config = new \Magento\Framework\Object();
        }

        return $this->_config;
    }

    /**
     * Retrieve full uploader SWF's file URL
     * Implemented to solve problem with cross domain SWFs
     * Now uploader can be only in the same URL where backend located
     *
     * @param string $url url to uploader in current theme
     * @return string full URL
     */
    public function getUploaderUrl($url)
    {
        return $this->_assetRepo->getUrl($url);
    }
}
