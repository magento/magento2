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
 * @category    Phoenix
 * @package     Phoenix_Moneybookers
 * @copyright   Copyright (c) 2013 Phoenix Medien GmbH & Co. KG (http://www.phoenix-medien.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Phoenix_Moneybookers_Block_Form extends Mage_Payment_Block_Form
{
    /**
     * Available locales for content URL generation
     *
     * @var array
     */
    protected $_supportedInfoLocales = array('de');

    /**
     * Default locale for content URL generation
     *
     * @var string
     */
    protected $_defaultInfoLocale = 'en';

    protected $_template = 'form.phtml';

    /**
     * Return payment logo image src
     *
     * @param string $payment Payment Code
     * @return string|bool
     */
    public function getPaymentImageSrc($payment)
    {
        if ($payment == 'moneybookers_obt') {
            $payment .= '_'.$this->getInfoLocale();
        }

        $design = Mage::getDesign();
        $images = array(
            'Phoenix_Moneybookers::images/' . $payment . '.png',
            'Phoenix_Moneybookers::images/' . $payment . '.gif'
        );
        foreach ($images as $image) {
            if (file_exists($design->getViewFile($image))) {
                return $design->getViewFileUrl($image);
            }
        }
        return false;
    }

    /**
     * Return supported locale for information text
     *
     * @return string
     */
    public function getInfoLocale()
    {
        $locale = substr(Mage::app()->getLocale()->getLocaleCode(), 0 ,2);
        if (!in_array($locale, $this->_supportedInfoLocales)) {
            $locale = $this->_defaultInfoLocale;
        }
        return $locale;
    }

    /**
     * Return info URL for eWallet payment
     *
     * @return string
     */
    public function getWltInfoUrl()
    {
        $locale = substr(Mage::app()->getLocale()->getLocaleCode(), 0 ,2);
        return 'http://www.moneybookers.com/app/?l=' . strtoupper($locale);
    }
}
