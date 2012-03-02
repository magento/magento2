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
 * @package     Mage_XmlConnect
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * XmlConnect device helper for iPhone
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Helper_Iphone extends Mage_Core_Helper_Abstract
{
    /**
     * Submission title length
     */
    const SUBMISSION_TITLE_LENGTH = 12;

    /**
     * Submission description length
     */
    const SUBMISSION_DESCRIPTION_LENGTH = 500;

    /**
     * Country renderer for submission page
     */
    const SUBMISSION_COUNTRY_RENDERER = 'istore';

    /**
     * Country columns for submission page
     */
    const SUBMISSION_COUNTRY_COLUMNS = 4;

    /**
     * Submit images that are stored in "params" field of history table
     *
     * @var array
     */
    protected $_imageIds = array(
        'icon', 'loader_image', 'loader_image_i4', 'logo', 'logo_i4', 'big_logo', 'big_logo_i4'
    );

    /**
     * List of coutries that allowed in Ituens by Apple Store
     *
     * array(
     *      'country name' => 'country id at directory model'
     * )
     *
     * @var array
     */
    protected $_allowedCountries = array(
        'Argentina'     => 'AR',
        'Armenia'       => 'AM',
        'Australia'     => 'AU',
        'Austria'       => 'AT',
        'Belgium'       => 'BE',
        'Botswana'      => 'BW',
        'Brazil'        => 'BR',
        'Bulgaria'      => 'BG',
        'Canada'        => 'CA',
        'Chile'         => 'CL',
        'China'         => 'CN',
        'Colombia'      => 'CO',
        'Costa Rica'    => 'CR',
        'Croatia'       => 'HR',
        'Czech Republic' => 'CZ',
        'Denmark'       => 'DK',
        'Dominican Republic' => 'DO',
        'Ecuador'       => 'EC',
        'Egypt'         => 'EG',
        'El Salvador'   => 'SV',
        'Estonia'       => 'EE',
        'Finland'       => 'FI',
        'France'        => 'FR',
        'Germany'       => 'DE',
        'Greece'        => 'GR',
        'Guatemala'     => 'GT',
        'Honduras'      => 'HN',
        'Hong Kong SAR China' => 'HK',
        'Hungary'       => 'HU',
        'India'         => 'IN',
        'Indonesia'     => 'ID',
        'Ireland'       => 'IE',
        'Israel'        => 'IL',
        'Italy'         => 'IT',
        'Jamaica'       => 'JM',
        'Japan'         => 'JP',
        'Jordan'        => 'JO',
        'Kazakstan'     => 'KZ',
        'Kenya'         => 'KE',
        'South Korea'   => 'KR',
        'Kuwait'        => 'KW',
        'Latvia'        => 'LV',
        'Lebanon'       => 'LB',
        'Lithuania'     => 'LT',
        'Luxembourg'    => 'LU',
        'Macau SAR China' => 'MO',
        'Macedonia'     => 'MK',
        'Madagascar'    => 'MG',
        'Malaysia'      => 'MY',
        'Mali'          => 'ML',
        'Malta'         => 'MT',
        'Mauritius'     => 'MU',
        'Mexico'        => 'MX',
        'Moldova'       => 'MD',
        'Netherlands'   => 'NL',
        'New Zealand'   => 'NZ',
        'Nicaragua'     => 'NI',
        'Niger'         => 'NE',
        'Norway'        => 'NO',
        'Pakistan'      => 'PK',
        'Panama'        => 'PA',
        'Paraguay'      => 'PY',
        'Peru'          => 'PE',
        'Philippines'   => 'PH',
        'Poland'        => 'PL',
        'Portugal'      => 'PT',
        'Qatar'         => 'QA',
        'Romania'       => 'RO',
        'Russia'        => 'RU',
        'Saudi Arabia'  => 'SA',
        'Senegal'       => 'SN',
        'Singapore'     => 'SG',
        'Slovakia'      => 'SK',
        'Slovenia'      => 'SI',
        'South Africa'  => 'ZA',
        'Spain'         => 'ES',
        'Sri Lanka'     => 'LK',
        'Sweden'        => 'SE',
        'Switzerland'   => 'CH',
        'Taiwan'        => 'TW',
        'Thailand'      => 'TH',
        'Tunisia'       => 'TN',
        'Turkey'        => 'TR',
        'Uganda'        => 'UG',
        'United Arab Emirates' => 'AE',
        'United Kingdom' => 'GB',
        'United States' => 'US',
        'Uruguay'       => 'UY',
        'Venezuela'     => 'VE',
        'Vietnam'       => 'VN',
    );

    /**
     * Country field renderer
     *
     * @var Mage_XmlConnect_Block_Adminhtml_Mobile_Submission_Renderer_Country_Istore
     */
    protected $_countryRenderer = null;

    /**
     * Get submit images that are required for application submit
     *
     * @return array
     */
    public function getSubmitImages()
    {
        return $this->_imageIds;
    }

    /**
     * Get default application tabs
     *
     * @return array
     */
    public function getDefaultDesignTabs()
    {
        if (!isset($this->_tabs)) {
            $design = Mage::getDesign();
            $this->_tabs = array(
                array(
                    'label' => Mage::helper('Mage_XmlConnect_Helper_Data')->__('Home'),
                    'image' => $design->getSkinUrl('Mage_XmlConnect::images/tab_home.png'),
                    'action' => 'Home',
                ),
                array(
                    'label' => Mage::helper('Mage_XmlConnect_Helper_Data')->__('Shop'),
                    'image' => $design->getSkinUrl('Mage_XmlConnect::images/tab_shop.png'),
                    'action' => 'Shop',
                ),
                array(
                    'label' => Mage::helper('Mage_XmlConnect_Helper_Data')->__('Search'),
                    'image' => $design->getSkinUrl('Mage_XmlConnect::images/tab_search.png'),
                    'action' => 'Search',
                ),
                array(
                    'label' => Mage::helper('Mage_XmlConnect_Helper_Data')->__('Cart'),
                    'image' => $design->getSkinUrl('Mage_XmlConnect::images/tab_cart.png'),
                    'action' => 'Cart',
                ),
                array(
                    'label' => Mage::helper('Mage_XmlConnect_Helper_Data')->__('More'),
                    'image' => $design->getSkinUrl('Mage_XmlConnect::images/tab_more.png'),
                    'action' => 'More',
                ),
                array(
                    'label' => Mage::helper('Mage_XmlConnect_Helper_Data')->__('Account'),
                    'image' => $design->getSkinUrl('Mage_XmlConnect::images/tab_account.png'),
                    'action' => 'Account',
                ),
                array(
                    'label' => Mage::helper('Mage_XmlConnect_Helper_Data')->__('More Info'),
                    'image' => $design->getSkinUrl('Mage_XmlConnect::images/tab_page.png'),
                    'action' => 'AboutUs',
                ),
            );
        }
        return $this->_tabs;
    }

    /**
     * Default application configuration
     *
     * @return array
     */
     public function getDefaultConfiguration()
     {
         return array(
             'native' => array(
                 'body' => array(
                     'backgroundColor' => '#ABABAB',
                     'scrollBackgroundColor' => '#EDEDED',
                 ),
                 'itemActions' => array(
                     'relatedProductBackgroundColor' => '#404040',
                 ),
                 'fonts' => array(
                     'Title1' => array(
                         'name' => 'HelveticaNeue-Bold',
                         'size' => '14',
                         'color' => '#FEFEFE',
                     ),
                     'Title2' => array(
                         'name' => 'HelveticaNeue-Bold',
                         'size' => '12',
                         'color' => '#222222',
                     ),
                     'Title3' => array(
                         'name' => 'HelveticaNeue',
                         'size' => '13',
                         'color' => '#000000',
                     ),
                     'Title4' => array(
                         'name' => 'HelveticaNeue',
                         'size' => '12',
                         'color' => '#FFFFFF',
                     ),
                     'Title5' => array(
                         'name' => 'HelveticaNeue-Bold',
                         'size' => '13',
                         'color' => '#dc5f02',
                     ),
                     'Title6' => array(
                         'name' => 'HelveticaNeue-Bold',
                         'size' => '16',
                         'color' => '#222222',
                     ),
                     'Title7' => array(
                         'name' => 'HelveticaNeue-Bold',
                         'size' => '13',
                         'color' => '#000000',
                     ),
                     'Title8' => array(
                         'name' => 'HelveticaNeue-Bold',
                         'size' => '11',
                         'color' => '#FFFFFF',
                     ),
                     'Title9' => array(
                         'name' => 'HelveticaNeue-Bold',
                         'size' => '12',
                         'color' => '#FFFFFF',
                     ),
                     'Text1' => array(
                         'name' => 'HelveticaNeue-Bold',
                         'size' => '12',
                         'color' => '#777777',
                     ),
                     'Text2' => array(
                         'name' => 'HelveticaNeue',
                         'size' => '10',
                         'color' => '#555555',
                     ),
                 ),
             ),
         );
     }

    /**
     * List of allowed fonts for iPhone application
     *
     * @return array
     */
    public function getFontList()
    {
        return array(
            array(
                'value' => 'HiraKakuProN-W3',
                'label' => 'HiraKakuProN-W3',
            ),
            array(
                'value' => 'Courier',
                'label' => 'Courier',
            ),
            array(
                'value' => 'Courier-BoldOblique',
                'label' => 'Courier-BoldOblique',
            ),
            array(
                'value' => 'Courier-Oblique',
                'label' => 'Courier-Oblique',
            ),
            array(
                'value' => 'Courier-Bold',
                'label' => 'Courier-Bold',
            ),
            array(
                'value' => 'ArialMT',
                'label' => 'ArialMT',
            ),
            array(
                'value' => 'Arial-BoldMT',
                'label' => 'Arial-BoldMT',
            ),
            array(
                'value' => 'Arial-BoldItalicMT',
                'label' => 'Arial-BoldItalicMT',
            ),
            array(
                'value' => 'Arial-ItalicMT',
                'label' => 'Arial-ItalicMT',
            ),
            array(
                'value' => 'STHeitiTC-Light',
                'label' => 'STHeitiTC-Light',
            ),
            array(
                'value' => 'STHeitiTC-Medium',
                'label' => 'STHeitiTC-Medium',
            ),
            array(
                'value' => 'AppleGothic',
                'label' => 'AppleGothic',
            ),
            array(
                'value' => 'CourierNewPS-BoldMT',
                'label' => 'CourierNewPS-BoldMT',
            ),
            array(
                'value' => 'CourierNewPS-ItalicMT',
                'label' => 'CourierNewPS-ItalicMT',
            ),
            array(
                'value' => 'CourierNewPS-BoldItalicMT',
                'label' => 'CourierNewPS-BoldItalicMT',
            ),
            array(
                'value' => 'CourierNewPSMT',
                'label' => 'CourierNewPSMT',
            ),
            array(
                'value' => 'Zapfino',
                'label' => 'Zapfino',
            ),
            array(
                'value' => 'HiraKakuProN-W6',
                'label' => 'HiraKakuProN-W6',
            ),
            array(
                'value' => 'ArialUnicodeMS',
                'label' => 'ArialUnicodeMS',
            ),
            array(
                'value' => 'STHeitiSC-Medium',
                'label' => 'STHeitiSC-Medium',
            ),
            array(
                'value' => 'STHeitiSC-Light',
                'label' => 'STHeitiSC-Light',
            ),
            array(
                'value' => 'AmericanTypewriter',
                'label' => 'AmericanTypewriter',
            ),
            array(
                'value' => 'AmericanTypewriter-Bold',
                'label' => 'AmericanTypewriter-Bold',
            ),
            array(
                'value' => 'Helvetica-Oblique',
                'label' => 'Helvetica-Oblique',
            ),
            array(
                'value' => 'Helvetica-BoldOblique',
                'label' => 'Helvetica-BoldOblique',
            ),
            array(
                'value' => 'Helvetica',
                'label' => 'Helvetica',
            ),
            array(
                'value' => 'Helvetica-Bold',
                'label' => 'Helvetica-Bold',
            ),
            array(
                'value' => 'MarkerFelt-Thin',
                'label' => 'MarkerFelt-Thin',
            ),
            array(
                'value' => 'HelveticaNeue',
                'label' => 'HelveticaNeue',
            ),
            array(
                'value' => 'HelveticaNeue-Bold',
                'label' => 'HelveticaNeue-Bold',
            ),
            array(
                'value' => 'DBLCDTempBlack',
                'label' => 'DBLCDTempBlack',
            ),
            array(
                'value' => 'Verdana-Bold',
                'label' => 'Verdana-Bold',
            ),
            array(
                'value' => 'Verdana-BoldItalic',
                'label' => 'Verdana-BoldItalic',
            ),
            array(
                'value' => 'Verdana',
                'label' => 'Verdana',
            ),
            array(
                'value' => 'Verdana-Italic',
                'label' => 'Verdana-Italic',
            ),
            array(
                'value' => 'TimesNewRomanPSMT',
                'label' => 'TimesNewRomanPSMT',
            ),
            array(
                'value' => 'TimesNewRomanPS-BoldMT',
                'label' => 'TimesNewRomanPS-BoldMT',
            ),
            array(
                'value' => 'TimesNewRomanPS-BoldItalicMT',
                'label' => 'TimesNewRomanPS-BoldItalicMT',
            ),
            array(
                'value' => 'TimesNewRomanPS-ItalicMT',
                'label' => 'TimesNewRomanPS-ItalicMT',
            ),
            array(
                'value' => 'Georgia-Bold',
                'label' => 'Georgia-Bold',
            ),
            array(
                'value' => 'Georgia',
                'label' => 'Georgia',
            ),
            array(
                'value' => 'Georgia-BoldItalic',
                'label' => 'Georgia-BoldItalic',
            ),
            array(
                'value' => 'Georgia-Italic',
                'label' => 'Georgia-Italic',
            ),
            array(
                'value' => 'STHeitiJ-Medium',
                'label' => 'STHeitiJ-Medium',
            ),
            array(
                'value' => 'STHeitiJ-Light',
                'label' => 'STHeitiJ-Light',
            ),
            array(
                'value' => 'ArialRoundedMTBold',
                'label' => 'ArialRoundedMTBold',
            ),
            array(
                'value' => 'TrebuchetMS-Italic',
                'label' => 'TrebuchetMS-Italic',
            ),
            array(
                'value' => 'TrebuchetMS',
                'label' => 'TrebuchetMS',
            ),
            array(
                'value' => 'Trebuchet-BoldItalic',
                'label' => 'Trebuchet-BoldItalic',
            ),
            array(
                'value' => 'TrebuchetMS-Bold',
                'label' => 'TrebuchetMS-Bold',
            ),
            array(
                'value' => 'STHeitiK-Medium',
                'label' => 'STHeitiK-Medium',
            ),
            array(
                'value' => 'STHeitiK-Light',
                'label' => 'STHeitiK-Light',
            ),
        );
    }

    /**
     * List of allowed font sizes for iPhone application
     *
     * @return array
     */
    public function getFontSizes()
    {
        $result = array();
        for ($i = 6; $i < 32; $i++) {
            $result[] = array(
                'value' => $i,
                'label' => $i . ' pt',
            );
        }
        return $result;
    }

    /**
     * Get list of countries that allowed in Itunes by Apple Store for Iphone
     *
     * @return array
     */
    public function getItunesCountriesArray()
    {
        return $this->_allowedCountries;
    }

    /**
     * Validate submit application data
     *
     * @param array $params
     * @return array
     */
    public function validateSubmit($params)
    {
        $errors = array();

        if (!Zend_Validate::is(isset($params['title']) ? $params['title'] : null, 'NotEmpty')) {
            $errors[] = Mage::helper('Mage_XmlConnect_Helper_Data')->__('Please enter the Title.');
        }

        if (isset($params['title'])) {
            $titleLength = self::SUBMISSION_TITLE_LENGTH;
            $strRules = array('min' => '1', 'max' => $titleLength);
            if (!Zend_Validate::is($params['title'], 'StringLength', $strRules)) {
                $errors[] = Mage::helper('Mage_XmlConnect_Helper_Data')->__('"Title" is more than %d characters long', $strRules['max']);
            }
        }

        if (!Zend_Validate::is(isset($params['description']) ? $params['description'] : null, 'NotEmpty')) {
            $errors[] = Mage::helper('Mage_XmlConnect_Helper_Data')->__('Please enter the Description.');
        }

        if (isset($params['description'])) {
            $descriptionLength = self::SUBMISSION_DESCRIPTION_LENGTH;
            $strRules = array('min' => '1', 'max' => $descriptionLength);
            if (!Zend_Validate::is($params['title'], 'StringLength', $strRules)) {
                $errors[] = Mage::helper('Mage_XmlConnect_Helper_Data')->__('"Description" is more than %d characters long', $strRules['max']);
            }
        }

        if (!Zend_Validate::is(isset($params['copyright']) ? $params['copyright'] : null, 'NotEmpty')) {
            $errors[] = Mage::helper('Mage_XmlConnect_Helper_Data')->__('Please enter the Copyright.');
        }

        if (empty($params['price_free'])) {
            if (!Zend_Validate::is(isset($params['price']) ? $params['price'] : null, 'NotEmpty')) {
                $errors[] = Mage::helper('Mage_XmlConnect_Helper_Data')->__('Please enter the Price.');
            }
        }

        if (!Zend_Validate::is(isset($params['country']) ? $params['country'] : null, 'NotEmpty')) {
            $errors[] = Mage::helper('Mage_XmlConnect_Helper_Data')->__('Please select at least one country.');
        }

        $keyLenght = Mage_XmlConnect_Model_Application::APP_MAX_KEY_LENGTH;
        if (Mage::helper('Mage_XmlConnect_Helper_Data')->getApplication()->getIsResubmitAction()) {
            if (isset($params['resubmission_activation_key'])) {
                $resubmissionKey = $params['resubmission_activation_key'];
            } else {
                $resubmissionKey = null;
            }

            if (!Zend_Validate::is($resubmissionKey, 'NotEmpty')) {
                $errors[] = Mage::helper('Mage_XmlConnect_Helper_Data')->__('Please enter the Resubmission Key.');
            } elseif (!Zend_Validate::is($resubmissionKey, 'StringLength', array(1, $keyLenght))) {
                $errors[] = Mage::helper('Mage_XmlConnect_Helper_Data')->__('Submit App failure. Invalid activation key provided');
            }
        } else {
            $key = isset($params['key']) ? $params['key'] : null;
            if (!Zend_Validate::is($key, 'NotEmpty')) {
                $errors[] = Mage::helper('Mage_XmlConnect_Helper_Data')->__('Please enter the Activation Key.');
            } elseif (!Zend_Validate::is($key, 'StringLength', array(1, $keyLenght))) {
                $errors[] = Mage::helper('Mage_XmlConnect_Helper_Data')->__('Submit App failure. Invalid activation key provided');
            }
        }
        return $errors;
    }

    /**
     * Check config for valid values
     *
     * @param array $native
     * @return array
     */
    public function validateConfig($native)
    {
        $errors = array();

        if ($native === false || (!isset($native['navigationBar']['icon'])
            || !Zend_Validate::is($native['navigationBar']['icon'], 'NotEmpty'))
        ) {
            $errors[] = Mage::helper('Mage_XmlConnect_Helper_Data')->__('Please upload  an image for "Logo in Header" field from Design Tab.');
        }

        if (!Mage::helper('Mage_XmlConnect_Helper_Data')->validateConfFieldNotEmpty('bannerImage', $native)) {
            $errors[] = Mage::helper('Mage_XmlConnect_Helper_Data')->__('Please upload  an image for "Banner on Home Screen" field from Design Tab.');
        }

        if (!Mage::helper('Mage_XmlConnect_Helper_Data')->validateConfFieldNotEmpty('backgroundImage', $native)) {
            $errors[] = Mage::helper('Mage_XmlConnect_Helper_Data')->__('Please upload  an image for "App Background" field from Design Tab.');
        }

        return $errors;
    }

    /**
     * Get renderer for submission country
     *
     * @return Mage_XmlConnect_Block_Adminhtml_Mobile_Submission_Renderer_Country_Istore
     */
    public function getCountryRenderer()
    {
        if (empty($this->_countryRenderer)) {
            $renderer = 'Mage_XmlConnect_Block_Adminhtml_Mobile_Submission_Renderer_Country_Istore';
            $this->_countryRenderer = Mage::app()->getLayout()->createBlock($renderer);
        }
        return $this->_countryRenderer;
    }

    /**
     * Get label for submission country
     *
     * @return string
     */
    public function getCountryLabel()
    {
        return Mage::helper('Mage_XmlConnect_Helper_Data')->__('App Stores');
    }

    /**
     * Get columns for submission country
     *
     * @return int
     */
    public function getCountryColumns()
    {
        return self::SUBMISSION_COUNTRY_COLUMNS;
    }

    /**
     * Get placement of Country Names for submission country
     *
     * @return bool
     */
    public function isCountryNamePlaceLeft()
    {
        return true;
    }

    /**
     * Get class name for submission country
     *
     * @return string
     */
    public function getCountryClass()
    {
        return self::SUBMISSION_COUNTRY_RENDERER . ' stripy';
    }

    /**
     * Check image fields
     *
     * We set empty value for image field if file was missed in some reason
     *
     * @param array $data
     * @return array
     */
    public function checkImages(array $data)
    {
        /** @var $helper Mage_XmlConnect_Helper_Image */
        $helper = Mage::helper('Mage_XmlConnect_Helper_Image');

        $icon =& $data['conf']['native']['navigationBar']['icon'];

        if (!empty($icon) && !$helper->checkAndGetImagePath($icon)) {
            $icon = '';
        }

        $banner =& $data['conf']['native']['body']['bannerImage'];

        if (!empty($banner) && !$helper->checkAndGetImagePath($banner)) {
            $banner = '';
        }

        $background =& $data['conf']['native']['body']['backgroundImage'];

        if (!empty($background) && !$helper->checkAndGetImagePath($background)) {
            $background = '';
        }

        return $data;
    }

    /**
     * Check required fields of a config for a front-end
     *
     * @throws Mage_Core_Exception
     * @param array $data
     * @return null
     */
    public function checkRequiredConfigFields($data)
    {
        if (!is_array($data)) {
            return;
        }

        if (isset($data['navigationBar']['icon']) && empty($data['navigationBar']['icon'])) {
            Mage::throwException(Mage::helper('Mage_XmlConnect_Helper_Data')->__('Logo in Header image missing.'));
        }
        if (isset($data['body']['bannerImage']) && empty($data['body']['bannerImage'])) {
            Mage::throwException(Mage::helper('Mage_XmlConnect_Helper_Data')->__('Banner on Home Screen image missing.'));
        }
        if (isset($data['body']['backgroundImage']) && empty($data['body']['backgroundImage'])) {
            Mage::throwException(Mage::helper('Mage_XmlConnect_Helper_Data')->__('App Background image missing.'));
        }
    }

    /**
     * Check the notifications are allowed for current type of application
     *
     * @return bool
     */
    public function isNotificationsAllowed()
    {
        return true;
    }
}
