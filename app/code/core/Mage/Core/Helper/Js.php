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
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * JavaScript helper
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Core_Helper_Js extends Mage_Core_Helper_Abstract
{
    /**
     * Array of senteces of JS translations
     *
     * @var array
     */
    protected $_translateData = null;

    /**
     * Retrieve JSON of JS sentences translation
     *
     * @return string
     */
    public function getTranslateJson()
    {
        return Mage::helper('Mage_Core_Helper_Data')->jsonEncode($this->_getTranslateData());
    }

    /**
     * Retrieve JS translator initialization javascript
     *
     * @return string
     */
    public function getTranslatorScript()
    {
        $script = 'var Translator = new Translate('.$this->getTranslateJson().');';
        return $this->getScript($script);
    }

    /**
     * Retrieve framed javascript
     *
     * @param   string $script
     * @return  script
     */
    public function getScript($script)
    {
        return '<script type="text/javascript">'.$script.'</script>';
    }

    /**
     * Retrieve javascript include code
     *
     * @param   string $file
     * @return  string
     */
    public function includeScript($file)
    {
        return '<script type="text/javascript" src="' . Mage::getDesign()->getSkinUrl($file) . '"></script>' . "\n";
    }

    /**
     * Retrieve JS translation array
     *
     * @return array
     */
    protected function _getTranslateData()
    {
        if ($this->_translateData ===null) {
            $this->_translateData = array(
                'Please select an option.' => $this->__('Please select an option.'),
                'This is a required field.' => $this->__('This is a required field.'),
                'Please enter a valid number in this field.' => $this->__('Please enter a valid number in this field.'),
                'Please use numbers only in this field. Please avoid spaces or other characters such as dots or commas.' =>
                    $this->__('Please use numbers only in this field. Please avoid spaces or other characters such as dots or commas.'),
                'Please use letters only (a-z) in this field.' => $this->__('Please use letters only (a-z) in this field.'),
                'Please use only letters (a-z), numbers (0-9) or underscore(_) in this field, first character should be a letter.' =>
                    $this->__('Please use only letters (a-z), numbers (0-9) or underscore(_) in this field, first character should be a letter.'),
                'Please use only letters (a-z) or numbers (0-9) only in this field. No spaces or other characters are allowed.' =>
                    $this->__('Please use only letters (a-z) or numbers (0-9) only in this field. No spaces or other characters are allowed.'),
                'Please use only letters (a-z) or numbers (0-9) or spaces and # only in this field.' =>
                    $this->__('Please use only letters (a-z) or numbers (0-9) or spaces and # only in this field.'),
                'Please enter a valid phone number. For example (123) 456-7890 or 123-456-7890.' =>
                    $this->__('Please enter a valid phone number. For example (123) 456-7890 or 123-456-7890.'),
                'Please enter a valid date.' => $this->__('Please enter a valid date.'),
                'Please enter a valid email address. For example johndoe@domain.com.' =>
                    $this->__('Please enter a valid email address. For example johndoe@domain.com.'),
                'Please enter 6 or more characters.' => $this->__('Please enter 6 or more characters.'),
                'Please make sure your passwords match.' => $this->__('Please make sure your passwords match.'),
                'Please enter a valid URL. Protocol is required (http://, https:// or ftp://)' =>
                    $this->__('Please enter a valid URL. Protocol is required (http://, https:// or ftp://)'),
                'Please enter a valid URL. For example http://www.example.com or www.example.com' =>
                    $this->__('Please enter a valid URL. For example http://www.example.com or www.example.com'),
                'Please enter a valid social security number. For example 123-45-6789.' =>
                    $this->__('Please enter a valid social security number. For example 123-45-6789.'),
                'Please enter a valid zip code. For example 90602 or 90602-1234.' =>
                    $this->__('Please enter a valid zip code. For example 90602 or 90602-1234.'),
                'Please enter a valid zip code.' => $this->__('Please enter a valid zip code.'),
                'Please use this date format: dd/mm/yyyy. For example 17/03/2006 for the 17th of March, 2006.' =>
                    $this->__('Please use this date format: dd/mm/yyyy. For example 17/03/2006 for the 17th of March, 2006.'),
                'Please enter a valid $ amount. For example $100.00.' =>
                    $this->__('Please enter a valid $ amount. For example $100.00.'),
                'Please select one of the above options.' => $this->__('Please select one of the above options.'),
                'Please select one of the options.' => $this->__('Please select one of the options.'),
                'Please enter a valid number in this field.' => $this->__('Please enter a valid number in this field.'),
                'Please select State/Province.' => $this->__('Please select State/Province.'),
                'Please enter valid password.' => $this->__('Please enter valid password.'),
                'Please enter 6 or more characters. Leading or trailing spaces will be ignored.' =>
                    $this->__('Please enter 6 or more characters. Leading or trailing spaces will be ignored.'),
                'Please use letters only (a-z or A-Z) in this field.' => $this->__('Please use letters only (a-z or A-Z) in this field.'),
                'Please enter a number greater than 0 in this field.' =>
                    $this->__('Please enter a number greater than 0 in this field.'),
                'Please enter a valid credit card number.' => $this->__('Please enter a valid credit card number.'),
                'Please wait, loading...' => $this->__('Please wait, loading...'),
                'Please choose to register or to checkout as a guest' => $this->__('Please choose to register or to checkout as a guest'),
                'Error: Passwords do not match' => $this->__('Error: Passwords do not match'),
                'Your order cannot be completed at this time as there is no shipping methods available for it. Please make necessary changes in your shipping address.' =>
                    $this->__('Your order cannot be completed at this time as there is no shipping methods available for it. Please make necessary changes in your shipping address.'),
                'Please specify shipping method.' => $this->__('Please specify shipping method.'),
                'Your order cannot be completed at this time as there is no payment methods available for it.' =>
                    $this->__('Your order cannot be completed at this time as there is no payment methods available for it.'),
                'Please specify payment method.' => $this->__('Please specify payment method.'),
                'Credit card number does not match credit card type.' => $this->__('Credit card number does not match credit card type.'),
                'Card type does not match credit card number.' => $this->__('Card type does not match credit card number.'),
                'Please enter a valid credit card verification number.' => $this->__('Please enter a valid credit card verification number.'),
                'Please use only letters (a-z or A-Z), numbers (0-9) or underscore(_) in this field, first character should be a letter.' =>
                    $this->__('Please use only letters (a-z or A-Z), numbers (0-9) or underscores (_) in this field, first character must be a letter.'),
                'Please input a valid CSS-length. For example 100px or 77pt or 20em or .5ex or 50%.' =>
                    $this->__('Please input a valid CSS-length. For example 100px or 77pt or 20em or .5ex or 50%.'),
                'Maximum length exceeded.' => $this->__('Maximum length exceeded.'),
                'Your session has been expired, you will be relogged in now.' => $this->__('Your session has been expired, you will be relogged in now.'),
                'Incorrect credit card expiration date.' => $this->__('Incorrect credit card expiration date.'),
                'This date is a required value.' => $this->__('This date is a required value.'),
                'The value is not within the specified range.' => $this->__('The value is not within the specified range.'),
                'Please use only letters (a-z or A-Z) or numbers (0-9) only in this field. No spaces or other characters are allowed.'
                    => $this->__('Please use only letters (a-z or A-Z) or numbers (0-9) only in this field. No spaces or other characters are allowed.'),
                'Please use only letters (a-z or A-Z) or numbers (0-9) or spaces and # only in this field.' =>
                    $this->__('Please use only letters (a-z or A-Z) or numbers (0-9) or spaces and # only in this field.'),
                'Please enter a valid fax number. For example (123) 456-7890 or 123-456-7890.' =>
                    $this->__('Please enter a valid fax number. For example (123) 456-7890 or 123-456-7890.'),
                'Please use only visible characters and spaces.' => $this->__('Please use only visible characters and spaces.'),
                'Please enter 7 or more characters. Password should contain both numeric and alphabetic characters.' =>
                    $this->__('Please enter 7 or more characters. Password should contain both numeric and alphabetic characters.'),
                'Please enter a valid URL Key. For example "example-page", "example-page.html" or "anotherlevel/example-page".' =>
                    $this->__('Please enter a valid URL Key. For example "example-page", "example-page.html" or "anotherlevel/example-page".'),
                'Please enter a valid XML-identifier. For example something_1, block5, id-4.' =>
                    $this->__('Please enter a valid XML-identifier. For example something_1, block5, id-4.'),
                'Please enter a number 0 or greater in this field.' => $this->__('Please enter a number 0 or greater in this field.'),
                'Text length does not satisfy specified text range.' => $this->__('Text length does not satisfy specified text range.'),
                'Please enter a number lower than 100.' => $this->__('Please enter a number lower than 100.'),
                'Please enter issue number or start date for switch/solo card type.' =>
                    $this->__('Please enter issue number or start date for switch/solo card type.'),
                'Please enter a valid day (1-%d).' => $this->__('Please enter a valid day (1-%d).'),
                'Please enter a valid month (1-12).' => $this->__('Please enter a valid month (1-12).'),
                'Please enter a valid year (1900-%d).' => $this->__('Please enter a valid year (1900-%d).'),
                'Please enter a valid full date' => $this->__('Please enter a valid full date'),
                'Please enter a valid date between %s and %s' =>
                    $this->__('Please enter a valid date between %s and %s'),
                'Please enter a valid date equal to or greater than %s' =>
                    $this->__('Please enter a valid date equal to or greater than %s'),
                'Please enter a valid date less than or equal to %s' =>
                    $this->__('Please enter a valid date less than or equal to %s')
            );
            foreach ($this->_translateData as $key=>$value) {
                if ($key == $value) {
                    unset($this->_translateData[$key]);
                }
            }
        }
        return $this->_translateData;
    }

}
