<?php
/**
 * Common functions needed for twig extension
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Core_Model_TemplateEngine_Twig_CommonFunctions
{
    /**
     * @var Mage_Core_Model_UrlInterface
     */
    private $_urlBuilder;

    /**
     * @var Mage_Core_Helper_Url
     */
    private $_urlHelper;

    /**
     * @var Mage_Core_Helper_Data
     */
    private $_dataHelper;

    /**
     * @var Mage_Core_Model_StoreManager
     */
    private $_storeManager;

    /**
     * @var Mage_Core_Model_View_Url
     */
    private $_viewUrl;

    /**
     * @var Mage_Core_Model_View_Config
     */
    private $_viewConfig;

    /**
     * @var Mage_Catalog_Helper_Image
     */
    private $_helperImage;

    /**
     * @var Mage_Core_Model_Logger
     */
    private $_logger;

    /**
     * @var Mage_Core_Model_LocaleInterface
     */
    private $_locale;

    public function __construct(
        Mage_Core_Model_UrlInterface $urlBuilder,
        Mage_Core_Helper_Url $urlHelper,
        Mage_Core_Helper_Data $dataHelper,
        Mage_Core_Model_StoreManager $storeManager,
        Mage_Core_Model_View_Url $viewUrl,
        Mage_Core_Model_View_Config $viewConfig,
        Mage_Catalog_Helper_Image $helperImage,
        Mage_Core_Model_Logger $logger,
        Mage_Core_Model_LocaleInterface $locale
    ) {
        $this->_urlBuilder = $urlBuilder;
        $this->_urlHelper = $urlHelper;
        $this->_dataHelper = $dataHelper;
        $this->_storeManager = $storeManager;
        $this->_viewUrl = $viewUrl;
        $this->_viewConfig = $viewConfig;
        $this->_helperImage = $helperImage;
        $this->_logger = $logger;
        $this->_locale = $locale;
    }

    /**
     * Returns a list of global functions to add to the existing list.
     *
     * @return array An array of global functions
     */
    public function getFunctions()
    {
        $options = array('is_safe' => array('html'));
        return array(
            new Twig_SimpleFunction('viewFileUrl', array($this, 'getViewFileUrl'), $options),
            new Twig_SimpleFunction('getSelectHtml', array($this, 'getSelectHtml'), $options),
            new Twig_SimpleFunction('getDateFormat', array($this->_locale, 'getDateFormat')),
            new Twig_SimpleFunction('getSelectFromToHtml', array($this, 'getSelectFromToHtml'), $options),
            new Twig_SimpleFunction('getUrl', array($this->_urlBuilder, 'getUrl'), $options),
            new Twig_SimpleFunction('encodeUrl', array($this->_urlHelper, 'getEncodedUrl'), $options),
            new Twig_SimpleFunction('getCurrentUrl', array($this->_urlHelper, 'getCurrentUrl'), $options),
            new Twig_SimpleFunction('isModuleOutputEnabled',
                array($this->_dataHelper, 'isModuleOutputEnabled'), $options),
            new Twig_SimpleFunction('getStoreConfig', array($this->_storeManager->getStore(), 'getConfig'), $options),
            new Twig_SimpleFunction('getDesignVarValue', array($this->_viewConfig->getViewConfig(), 'getVarValue'),
                $options),
            new Twig_SimpleFunction('getDefaultImage', array($this->_helperImage, 'getDefaultImage'), $options),
        );
    }

    /**
     * Retrieve url of themes file
     *
     * @param string $file path to file in theme
     * @param array $params
     * @return string
     * @throws Magento_Exception
     */
    public function getViewFileUrl($file = null, array $params = array())
    {
        try {
            return $this->_viewUrl->getViewFileUrl($file, $params);
        } catch (Magento_Exception $e) {
            $this->_logger->logException($e);
            return $this->_urlBuilder->getUrl('', array('_direct' => 'core/index/notfound'));
        }
    }

    /**
     * @param Mage_Core_Block_Html_Select $selectBlock
     * @param $identifier
     * @param $name
     * @param $nameOptionsById
     * @param null $selectedValue
     * @return mixed
     */
    public function getSelectHtml($selectBlock, $identifier, $name, $nameOptionsById, $selectedValue = null)
    {

        $options = array();
        foreach ($nameOptionsById as $value => $label) {
            $options[] = array('value' => $value, 'label' => $label);
        }
        return $this->_initSelectBlock($selectBlock, $identifier, $name, $nameOptionsById, $selectedValue)
            ->setOptions($options)
            ->getHtml();
    }

    /**
     * From Mage_Catalog_Block_Product_View_Options_Type_Date: Return drop-down html with range of values
     *
     * @param Mage_Core_Block_Html_Select $selectBlock
     * @param string $name Id/name of html select element
     * @param int $fromNumber  Start position
     * @param int $toNumber    End position
     * @param $nameOptionsById
     * @param $optionsId
     * @param null $value Value selected
     * @return string Formatted Html
     */
    public function getSelectFromToHtml(
        $selectBlock, $name, $fromNumber, $toNumber,
        $nameOptionsById, $optionsId, $value = null
    ) {
        $options = array(
            array('value' => '', 'label' => '-')
        );
        for ($i = $fromNumber; $i <= $toNumber; $i++) {
            $options[] = array('value' => $i, 'label' => $this->_getValueWithLeadingZeros($i));
        }
        return $this->_initSelectBlock($selectBlock, $optionsId, $name, $nameOptionsById, $value)
            ->setOptions($options)
            ->getHtml();
    }

    /**
     * Initializes values in the selection list.
     * From Mage_Catalog_Block_Product_View_Options_Type_Date: HTML select element
     *
     * @param Mage_Core_Block_Html_Select $selectBlock
     * @param $identifier
     * @param $name
     * @param $nameOptionsById
     * @param null $value
     * @return Mage_Core_Block_Html_Select
     */
    protected function _initSelectBlock($selectBlock, $identifier, $name, $nameOptionsById, $value = null)
    {
        $selectBlock->setId('options_' . $identifier . '_' . $name);
        $selectBlock->setClass('product-custom-option datetime-picker');
        $selectBlock->setExtraParams();
        $selectBlock->setName('options[' . $identifier . '][' . $name . ']');

        $extraParams = 'style="width:auto"';
        $selectBlock->setExtraParams($extraParams);

        if (is_null($value)) {
            $value = $nameOptionsById;
        }
        if (!is_null($value)) {
            $selectBlock->setValue($value);
        }

        return $selectBlock;
    }

    /**
     * From Mage_Catalog_Block_Product_View_Options_Type_Date: Add Leading Zeros to number less than 10
     *
     * @param int|string $value value to pad with zeros
     * @return string
     */
    protected function _getValueWithLeadingZeros($value)
    {
        return $value < 10 ? '0'.$value : $value;
    }
}
