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
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Adminhtml footer block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Page_Footer extends Mage_Adminhtml_Block_Template
{
    const LOCALE_CACHE_LIFETIME = 7200;
    const LOCALE_CACHE_KEY      = 'footer_locale';
    const LOCALE_CACHE_TAG      = 'adminhtml';

    protected function _construct()
    {
        $this->setTemplate('page/footer.phtml');
        $this->setShowProfiler(true);
    }

    public function getChangeLocaleUrl()
    {
        return $this->getUrl('adminhtml/index/changeLocale');
    }

    public function getUrlForReferer()
    {
        return $this->getUrlEncoded('*/*/*',array('_current'=>true));
    }

    public function getRefererParamName()
    {
        return Mage_Core_Controller_Varien_Action::PARAM_NAME_URL_ENCODED;
    }

    public function getLanguageSelect()
    {
        $locale  = Mage::app()->getLocale();
        $cacheId = self::LOCALE_CACHE_KEY . $locale->getLocaleCode();
        $html    = Mage::app()->loadCache($cacheId);

        if (!$html) {
            $html = $this->getLayout()->createBlock('Mage_Adminhtml_Block_Html_Select')
                ->setName('locale')
                ->setId('interface_locale')
                ->setTitle(Mage::helper('Mage_Page_Helper_Data')->__('Interface Language'))
                ->setExtraParams('style="width:200px"')
                ->setValue($locale->getLocaleCode())
                ->setOptions($locale->getTranslatedOptionLocales())
                ->getHtml();
            Mage::app()->saveCache($html, $cacheId, array(self::LOCALE_CACHE_TAG), self::LOCALE_CACHE_LIFETIME);
        }

        return $html;
    }
}
