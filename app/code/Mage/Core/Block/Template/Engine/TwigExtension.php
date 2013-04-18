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
 * @package     Mage_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Core_Block_Template_Engine_TwigExtension extends Twig_Extension
{
    /** @var \Mage_Core_Block_Template */
    protected $_block;

    /** @var \Mage_Core_Model_UrlInterface */
    protected $_urlBuilder;

    /** @var \Mage_Core_Helper_Data */
    protected $_dataHelper;

    /** @var \Mage_Core_Model_StoreManager */
    protected $_storeManager;

    /** @var \Mage_Core_Model_Design_Package */
    protected $_designPackage;

    /** @var \Mage_Catalog_Helper_Image */
    protected $_helperImage;

    /**
     * @param Mage_Core_Block_Template $block
     * @param Mage_Core_Model_UrlInterface $urlBuilder
     * @param Mage_Core_Helper_Url $urlHelper
     * @param Mage_Core_Helper_Data $dataHelper
     * @param Mage_Core_Model_StoreManager $storeManager
     * @param Mage_Core_Model_Design_Package $designPackage
     * @param Mage_Catalog_Helper_Image $helperImage
     */
    public function __construct(Mage_Core_Block_Template $block,
                                Mage_Core_Model_UrlInterface $urlBuilder,
                                Mage_Core_Helper_Url $urlHelper,
                                Mage_Core_Helper_Data $dataHelper,
                                Mage_Core_Model_StoreManager $storeManager,
                                Mage_Core_Model_Design_Package $designPackage,
                                Mage_Catalog_Helper_Image $helperImage)
    {
        $this->_block = $block;
        $this->_urlBuilder = $urlBuilder;
        $this->_urlHelper = $urlHelper;
        $this->_dataHelper = $dataHelper;
        $this->_storeManager = $storeManager;
        $this->_designPackage = $designPackage;
        $this->_helperImage = $helperImage;
    }

    public function getName()
    {
        return 'Magento';
    }

    /**
     * Returns a list of global functions to add to the existing list.
     *
     * @return array An array of global functions
     */
    public function getFunctions()
    {
        $options = array('is_safe' => array('html'));

        $urlEncodeFunction = function($url) {
            return strtr(base64_encode($url), '+/=', '-_,');
        };

        return array(
            new Twig_SimpleFunction('createBlock', array($this->_block->getLayout(), 'createBlock')),
			new Twig_SimpleFunction('executeRenderer', array($this->_block, 'executeRenderer'), $options),
            new Twig_SimpleFunction('getChildChildHtml', array($this->_block, 'getChildChildHtml'), $options),
			new Twig_SimpleFunction('getChildHtml', array($this->_block, 'getChildHtml'), $options),
            new Twig_SimpleFunction('getChildData', array($this->_block, 'getChildData'), $options),
        	new Twig_SimpleFunction('getDateFormat', array(Mage::app()->getLocale(), 'getDateFormat')),
            new Twig_SimpleFunction('getElementAlias', array($this->_block->getLayout(), 'getElementAlias'), $options),
            new Twig_SimpleFunction('getGroupChildNames', array($this->_block, 'getGroupChildNames'), $options),
            new Twig_SimpleFunction('getSelectHtml', array($this, '_getSelectHtml'), $options),
            new Twig_SimpleFunction('getMessagesHtml',
                array($this->_block->getMessagesBlock(), 'getGroupedHtml'), $options),
        	new Twig_SimpleFunction('getSelectFromToHtml', array($this, '_getSelectFromToHtml'), $options),
            new Twig_SimpleFunction('renderElement', array($this->_block->getLayout(), 'renderElement'), $options),
        	new Twig_SimpleFunction('viewFileUrl', array($this->_block, 'getViewFileUrl'), $options),
            new Twig_SimpleFunction('getUrl', array($this->_urlBuilder, 'getUrl'), $options),
            new Twig_SimpleFunction('encodeUrl', $urlEncodeFunction, $options),
            new Twig_SimpleFunction('getCurrentUrl', array($this->_urlHelper, 'getCurrentUrl'), $options),
            new Twig_SimpleFunction('isModuleOutputEnabled',
                array($this->_dataHelper, 'isModuleOutputEnabled'), $options),
            new Twig_SimpleFunction('getStoreConfig', array($this->_storeManager->getStore(), 'getConfig'), $options),
            new Twig_SimpleFunction('getDesignVarValue', array($this->_designPackage->getViewConfig(), 'getVarValue'),
                $options),
            new Twig_SimpleFunction('getDefaultImage', array($this->_helperImage, 'getDefaultImage'), $options),
        );
    }

    /**
     * From Mage_Catalog_Block_Product_View_Options_Type_Date: Return drop-down html with range of values
     *
     * @param $selectBlock
     * @param string $name Id/name of html select element
     * @param int $from  Start position
     * @param int $to    End position
     * @param $nameOptionsById
     * @param $optionsId
     * @param null $value Value selected
     * @return string Formatted Html
     */
    public function _getSelectFromToHtml($selectBlock, $name, $from, $to, $nameOptionsById,
            $optionsId, $value = null)
    {
        $options = array(
                array('value' => '', 'label' => '-')
        );
        for ($i = $from; $i <= $to; $i++) {
            $options[] = array('value' => $i, 'label' => $this->_getValueWithLeadingZeros(true, $i));
        }
        return $this->_initSelectBlock($selectBlock, $optionsId, $name, $nameOptionsById, $value)
            ->setOptions($options)
            ->getHtml();
    }

    /**
     * Initializes values in the selection list.
     * From Mage_Catalog_Block_Product_View_Options_Type_Date: HTML select element
     *
     * @param $selectBlock
     * @param $id
     * @param $name
     * @param $nameOptionsById
     * @param null $value
     * @return Mage_Core_Block_Html_Select
     */
    public function _initSelectBlock($selectBlock, $id, $name, $nameOptionsById, $value = null)
    {
        //$this->setSkipJsReloadPrice(1);

        // $require = $this->getOption()->getIsRequire() ? ' required-entry' : '';
        $require = '';
        $selectBlock->setId('options_' . $id . '_' . $name)
            ->setClass('product-custom-option datetime-picker' . $require)
            ->setExtraParams()
            ->setName('options[' . $id . '][' . $name . ']');

        $extraParams = 'style="width:auto"';
        //if (!$this->getSkipJsReloadPrice()) {
        //	$extraParams .= ' onchange="opConfig.reloadPrice()"';
        //}
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
     * @param $selectBlock
     * @param $id
     * @param $name
     * @param $nameOptionsById
     * @param null $selectedValue
     * @return mixed
     */
    public function _getSelectHtml($selectBlock, $id, $name, $nameOptionsById, $selectedValue = null) {

        $options = array();
        foreach ($nameOptionsById as $value => $label) {
            $options[] = array('value' => $value, 'label' => $label);
        }
        return $this->_initSelectBlock($selectBlock, $id, $name, $nameOptionsById, $selectedValue)
            ->setOptions($options)
            ->getHtml();
    }

    /**
     * From Mage_Catalog_Block_Product_View_Options_Type_Date: Add Leading Zeros to number less than 10
     *
     * @param int
     * @return string
     */
    protected static function _getValueWithLeadingZeros($fillLeadingZeros, $value)
    {
        if (!$fillLeadingZeros) {
            return $value;
        }
        return $value < 10 ? '0'.$value : $value;
    }
}