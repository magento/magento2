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
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml media library uploader
 */
class Mage_Adminhtml_Block_Media_Uploader extends Mage_Adminhtml_Block_Widget
{
    /**
     * @var Varien_Object
     */
    protected $_config;

    /**
     * @var string
     */
    protected $_template = 'media/uploader.phtml';

    /**
     * @var Mage_Core_Model_View_Url
     */
    protected $_viewUrl;

    /**
     * @param Mage_Backend_Block_Template_Context $context
     * @param Mage_Core_Model_View_Url $viewUrl
     * @param array $data
     */
    public function __construct(
        Mage_Backend_Block_Template_Context $context,
        Mage_Core_Model_View_Url $viewUrl,
        array $data = array()
    ) {
        $this->_viewUrl = $viewUrl;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        parent::_construct();

        $this->setId($this->getId() . '_Uploader');

        $this->getConfig()->setUrl(Mage::getModel('Mage_Backend_Model_Url')->addSessionParam()->getUrl('*/*/upload'));
        $this->getConfig()->setParams(array('form_key' => $this->getFormKey()));
        $this->getConfig()->setFileField('file');
        $this->getConfig()->setFilters(array(
            'images' => array(
                'label' => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Images (.gif, .jpg, .png)'),
                'files' => array('*.gif', '*.jpg', '*.png')
            ),
            'media' => array(
                'label' => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Media (.avi, .flv, .swf)'),
                'files' => array('*.avi', '*.flv', '*.swf')
            ),
            'all' => array(
                'label' => Mage::helper('Mage_Adminhtml_Helper_Data')->__('All Files'),
                'files' => array('*.*')
            )
        ));
    }

    /**
     * Prepares layout and set element renderer
     *
     * @return Mage_Adminhtml_Block_Media_Uploader
     */
    protected function _prepareLayout()
    {
        $head = $this->getLayout()->getBlock('head');
        if ($head) {
            $head->addCss('jquery/fileUploader/css/jquery.fileupload-ui.css');
        }
        return parent::_prepareLayout();
    }

    /**
     * Retrive uploader js object name
     *
     * @return string
     */
    public function getJsObjectName()
    {
        return $this->getHtmlId() . 'JsObject';
    }

    /**
     * Retrive config json
     *
     * @return string
     */
    public function getConfigJson()
    {
        return Mage::helper('Mage_Core_Helper_Data')->jsonEncode($this->getConfig()->getData());
    }

    /**
     * Retrive config object
     *
     * @return Varien_Config
     */
    public function getConfig()
    {
        if (null === $this->_config) {
            $this->_config = new Varien_Object();
        }

        return $this->_config;
    }

    /**
     * Retrieve full uploader SWF's file URL
     * Implemented to solve problem with cross domain SWFs
     * Now uploader can be only in the same URL where backend located
     *
     * @param string $url url to uploader in current theme
     *
     * @return string full URL
     */
    public function getUploaderUrl($url)
    {
        return $this->_viewUrl->getViewFileUrl($url);
    }
}
