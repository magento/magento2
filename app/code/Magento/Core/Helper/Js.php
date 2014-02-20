<?php
/**
 * JavaScript helper
 *
 * @author      Magento Core Team <core@magentocommerce.com>
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
 * @category    Magento
 * @package     Magento_Core
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Helper;

class Js extends \Magento\App\Helper\AbstractHelper
{
    /**
     * Array of sentences of JS translations
     *
     * @var array
     */
    protected $_translateData = null;

    /**
     * @var \Magento\View\Url
     */
    protected $_viewUrl;

    /**
     * Core data
     *
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreData = null;

    /**
     * @param \Magento\App\Helper\Context $context
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\View\Url $viewUrl
     */
    public function __construct(
        \Magento\App\Helper\Context $context,
        \Magento\Core\Helper\Data $coreData,
        \Magento\View\Url $viewUrl
    ) {
        $this->_coreData = $coreData;
        parent::__construct($context);
        $this->_viewUrl = $viewUrl;
    }

    /**
     * Retrieve JSON of JS sentences translation
     *
     * @return string
     */
    public function getTranslateJson()
    {
        return $this->_coreData->jsonEncode($this->getTranslateData());
    }

    /**
     * Retrieve JS translator initialization javascript
     *
     * @return string
     */
    public function getTranslatorScript()
    {
        $script = '(function($) {$.mage.translate.add(' . $this->getTranslateJson() . ')})(jQuery);';
        return $this->getScript($script);
    }

    /**
     * Retrieve framed javascript
     *
     * @param   string $script
     * @return  string
     */
    public function getScript($script)
    {
        return '<script type="text/javascript">//<![CDATA[' . "\n{$script}\n" . '//]]></script>';
    }

    /**
     * Retrieve javascript include code
     *
     * @param   string $file
     * @return  string
     */
    public function includeScript($file)
    {
        return '<script type="text/javascript" src="' . $this->_viewUrl->getViewFileUrl($file) . '"></script>' . "\n";
    }

    /**
     * Retrieve JS translation array
     *
     * @return array
     */
    public function getTranslateData()
    {
        if ($this->_translateData === null) {
            $this->_translateData = array();
            $this->_populateTranslateData();
        }
        return $this->_translateData;
    }

    /**
     * Helper function that populates _translateData with default values.
     *
     * @return void
     * @SuppressWarnings(PHPMD)
     */
    protected function _populateTranslateData()
    {
        // @codingStandardsIgnoreStart
        //flexuploader.js
        $this->_addTranslation('Complete', __('Complete'));
        $this->_addTranslation('Upload Security Error', __('Upload Security Error'));
        $this->_addTranslation('Upload HTTP Error', __('Upload HTTP Error'));
        $this->_addTranslation('Upload I/O Error', __('Upload I/O Error'));
        $this->_addTranslation('SSL Error: Invalid or self-signed certificate', __('SSL Error: Invalid or self-signed certificate'));
        $this->_addTranslation('TB', __('TB'));
        $this->_addTranslation('GB', __('GB'));
        $this->_addTranslation('MB', __('MB'));
        $this->_addTranslation('kB', __('kB'));
        $this->_addTranslation('B', __('B'));
        $this->_addTranslation('Add Products', __('Add Products'));
        //addbysku.js
        $this->_addTranslation('Add Products By SKU', __('Add Products By SKU'));
        $this->_addTranslation('Insert Widget...', __('Insert Widget...'));
        //rules.js
        $this->_addTranslation('Please wait, loading...', __('Please wait, loading...'));
        //validation.js
        $this->_addTranslation('HTML tags are not allowed', __('HTML tags are not allowed'));
        $this->_addTranslation('Please select an option.', __('Please select an option.'));
        $this->_addTranslation('This is a required field.', __('This is a required field.'));
        $this->_addTranslation('Please enter a valid number in this field.', __('Please enter a valid number in this field.'));
        $this->_addTranslation('The value is not within the specified range.', __('The value is not within the specified range.'));
        $this->_addTranslation('Please use numbers only in this field. Please avoid spaces or other characters such as dots or commas.', __('Please use numbers only in this field. Please avoid spaces or other characters such as dots or commas.'));
        $this->_addTranslation('Please use letters only (a-z or A-Z) in this field.', __('Please use letters only (a-z or A-Z) in this field.'));
        $this->_addTranslation('Please use only letters (a-z), numbers (0-9) or underscore(_) in this field, first character should be a letter.', __('Please use only letters (a-z), numbers (0-9) or underscore(_) in this field, first character should be a letter.'));
        $this->_addTranslation('Please use only letters (a-z or A-Z) or numbers (0-9) only in this field. No spaces or other characters are allowed.', __('Please use only letters (a-z or A-Z) or numbers (0-9) only in this field. No spaces or other characters are allowed.'));
        $this->_addTranslation('Please use only letters (a-z or A-Z) or numbers (0-9) or spaces and # only in this field.', __('Please use only letters (a-z or A-Z) or numbers (0-9) or spaces and # only in this field.'));
        $this->_addTranslation('Please enter a valid fax number. For example (123) 456-7890 or 123-456-7890.', __('Please enter a valid fax number. For example (123) 456-7890 or 123-456-7890.'));
        $this->_addTranslation('Please enter a valid date.', __('Please enter a valid date.'));
        $this->_addTranslation('The From Date value should be less than or equal to the To Date value.', __('The From Date value should be less than or equal to the To Date value.'));
        $this->_addTranslation('Please enter a valid email address. For example johndoe@domain.com.', __('Please enter a valid email address. For example johndoe@domain.com.'));
        $this->_addTranslation('Please use only visible characters and spaces.', __('Please use only visible characters and spaces.'));
        $this->_addTranslation('Please enter 6 or more characters. Leading or trailing spaces will be ignored.', __('Please enter 6 or more characters. Leading or trailing spaces will be ignored.'));
        $this->_addTranslation('Please enter 7 or more characters. Password should contain both numeric and alphabetic characters.', __('Please enter 7 or more characters. Password should contain both numeric and alphabetic characters.'));
        $this->_addTranslation('Please make sure your passwords match.', __('Please make sure your passwords match.'));
        $this->_addTranslation('Please enter a valid URL. Protocol is required (http://, https:// or ftp://)', __('Please enter a valid URL. Protocol is required (http://, https:// or ftp://)'));
        $this->_addTranslation('Please enter a valid URL Key. For example "example-page", "example-page.html" or "anotherlevel/example-page".', __('Please enter a valid URL Key. For example "example-page", "example-page.html" or "anotherlevel/example-page".'));
        $this->_addTranslation('Please enter a valid XML-identifier. For example something_1, block5, id-4.', __('Please enter a valid XML-identifier. For example something_1, block5, id-4.'));
        $this->_addTranslation('Please enter a valid social security number. For example 123-45-6789.', __('Please enter a valid social security number. For example 123-45-6789.'));
        $this->_addTranslation('Please enter a valid zip code. For example 90602 or 90602-1234.', __('Please enter a valid zip code. For example 90602 or 90602-1234.'));
        $this->_addTranslation('Please enter a valid zip code.', __('Please enter a valid zip code.'));
        $this->_addTranslation('Please use this date format: dd/mm/yyyy. For example 17/03/2006 for the 17th of March, 2006.', __('Please use this date format: dd/mm/yyyy. For example 17/03/2006 for the 17th of March, 2006.'));
        $this->_addTranslation('Please select one of the above options.', __('Please select one of the above options.'));
        $this->_addTranslation('Please select one of the options.', __('Please select one of the options.'));
        $this->_addTranslation('Please select State/Province.', __('Please select State/Province.'));
        $this->_addTranslation('Please enter a number greater than 0 in this field.', __('Please enter a number greater than 0 in this field.'));
        $this->_addTranslation('Please enter a number 0 or greater in this field.', __('Please enter a number 0 or greater in this field.'));
        $this->_addTranslation('Please enter a valid credit card number.', __('Please enter a valid credit card number.'));
        $this->_addTranslation('Credit card number does not match credit card type.', __('Credit card number does not match credit card type.'));
        $this->_addTranslation('Card type does not match credit card number.', __('Card type does not match credit card number.'));
        $this->_addTranslation('Incorrect credit card expiration date.', __('Incorrect credit card expiration date.'));
        $this->_addTranslation('Please enter a valid credit card verification number.', __('Please enter a valid credit card verification number.'));
        $this->_addTranslation('Please use only letters (a-z or A-Z), numbers (0-9) or underscore(_) in this field, first character should be a letter.', __('Please use only letters (a-z or A-Z), numbers (0-9) or underscore(_) in this field, first character should be a letter.'));
        $this->_addTranslation('Please input a valid CSS-length. For example 100px or 77pt or 20em or .5ex or 50%.', __('Please input a valid CSS-length. For example 100px or 77pt or 20em or .5ex or 50%.'));
        $this->_addTranslation('Text length does not satisfy specified text range.', __('Text length does not satisfy specified text range.'));
        $this->_addTranslation('Please enter a number lower than 100.', __('Please enter a number lower than 100.'));
        $this->_addTranslation('Please select a file', __('Please select a file'));
        $this->_addTranslation('Please enter issue number or start date for switch/solo card type.', __('Please enter issue number or start date for switch/solo card type.'));
        //js.js
        $this->_addTranslation('This date is a required value.', __('This date is a required value.'));
        $this->_addTranslation('Please enter a valid day (1-%d).', __('Please enter a valid day (1-%d).'));
        $this->_addTranslation('Please enter a valid month (1-12).', __('Please enter a valid month (1-12).'));
        $this->_addTranslation('Please enter a valid year (1900-%d).', __('Please enter a valid year (1900-%d).'));
        $this->_addTranslation('Please enter a valid full date', __('Please enter a valid full date'));
        //various files
        $this->_addTranslation('Allow', __('Allow'));
        $this->_addTranslation('Activate', __('Activate'));
        $this->_addTranslation('Reauthorize', __('Reauthorize'));
        $this->_addTranslation('Cancel', __('Cancel'));
        $this->_addTranslation('Done', __('Done'));
        $this->_addTranslation('Save', __('Save'));
        $this->_addTranslation('File extension not known or unsupported type.', __('File extension not known or unsupported type.'));
        $this->_addTranslation('Configure Product', __('Configure Product'));
        $this->_addTranslation('OK', __('OK'));
        $this->_addTranslation('Gift Options for ', __('Gift Options for '));
        $this->_addTranslation('New Option', __('New Option'));
        $this->_addTranslation('Add Products to New Option', __('Add Products to New Option'));
        $this->_addTranslation('Add Products to Option "%s"', __('Add Products to Option "%s"'));
        $this->_addTranslation('Add Selected Products', __('Add Selected Products'));
        $this->_addTranslation('Select type of option.', __('Select type of option.'));
        $this->_addTranslation('Please add rows to option.', __('Please add rows to option.'));
        $this->_addTranslation('Select Product', __('Select Product'));
        $this->_addTranslation('Import', __('Import'));
        $this->_addTranslation('Please select items.', __('Please select items.'));
        $this->_addTranslation('Add Products to Group', __('Add Products to Group'));
        $this->_addTranslation('start typing to search category', __('start typing to search category'));
        $this->_addTranslation('Choose existing category.', __('Choose existing category.'));
        $this->_addTranslation('Create Category', __('Create Category'));
        $this->_addTranslation('Sorry, there was an unknown error.', __('Sorry, there was an unknown error.'));
        $this->_addTranslation('Something went wrong while loading the theme.', __('Something went wrong while loading the theme.'));
        $this->_addTranslation('We don\'t recognize or support this file extension type.', __('We don\'t recognize or support this file extension type.'));
        $this->_addTranslation('Error', __('Error'));
        $this->_addTranslation('No stores were reassigned.', __('No stores were reassigned.'));
        $this->_addTranslation('Assign theme to your live store-view:', __('Assign theme to your live store-view:'));
        $this->_addTranslation('Default title', __('Default title'));
        $this->_addTranslation('The URL to assign stores is not defined.', __('The URL to assign stores is not defined.'));
        $this->_addTranslation('No', __('No'));
        $this->_addTranslation('Yes', __('Yes'));
        $this->_addTranslation('Some problem with revert action', __('Some problem with revert action'));
        $this->_addTranslation('Error: unknown error.', __('Error: unknown error.'));
        $this->_addTranslation('Some problem with save action', __('Some problem with save action'));
        $this->_addTranslation('Delete', __('Delete'));
        $this->_addTranslation('Folder', __('Folder'));
        $this->_addTranslation('Delete Folder', __('Delete Folder'));
        $this->_addTranslation('Are you sure you want to delete the folder named', __('Are you sure you want to delete the folder named'));
        $this->_addTranslation('Delete File', __('Delete File'));
        $this->_addTranslation('Method ', __('Method '));
        $this->_addTranslation('Please wait...', __('Please wait...'));
        $this->_addTranslation('Loading...', __('Loading...'));
        $this->_addTranslation('Translate', __('Translate'));
        $this->_addTranslation('Submit', __('Submit'));
        $this->_addTranslation('Close', __('Close'));
        $this->_addTranslation('Please enter a value less than or equal to %s.', __('Please enter a value less than or equal to %s.'));
        $this->_addTranslation('Please enter a value greater than or equal to %s.', __('Please enter a value greater than or equal to %s.'));
        $this->_addTranslation('Maximum length of this field must be equal or less than %s symbols.', __('Maximum length of this field must be equal or less than %s symbols.'));
        $this->_addTranslation('No records found.', __('No records found.'));
        $this->_addTranslation('Recent items', __('Recent items'));
        $this->_addTranslation('Show all...', __('Show all...'));
        $this->_addTranslation('Please enter a date in the past.', __('Please enter a date in the past.'));
        $this->_addTranslation('Please enter a date between %min and %max.', __('Please enter a date between %min and %max.'));
        // opcheckout.js
        $this->_addTranslation('Please choose to register or to checkout as a guest.', __('Please choose to register or to checkout as a guest.'));
        $this->_addTranslation('We are not able to ship to the selected shipping address. Please choose another address or edit the current address.', __('We are not able to ship to the selected shipping address. Please choose another address or edit the current address.'));
        $this->_addTranslation('Please specify a shipping method.', __('Please specify a shipping method.'));
        // opcheckout.js and payment.js
        $this->_addTranslation('We can\'t complete your order because you don\'t have a payment method available.', __('We can\'t complete your order because you don\'t have a payment method available.'));
        //multiple-wishlist.js
        $this->_addTranslation('Error happened while creating wishlist. Please try again later', __('Error happened while creating wishlist. Please try again later'));
        $this->_addTranslation('You must select items to move', __('You must select items to move'));
        $this->_addTranslation('You must select items to copy', __('You must select items to copy'));
        $this->_addTranslation('You are about to delete your wish list. This action cannot be undone. Are you sure you want to continue?', __('You are about to delete your wish list. This action cannot be undone. Are you sure you want to continue?'));
        //various files
        $this->_addTranslation('Please specify payment method.', __('Please specify payment method.'));
        $this->_addTranslation('Are you sure you want to delete this address?', __('Are you sure you want to delete this address?'));
        $this->_addTranslation('Use gift registry shipping address', __('Use gift registry shipping address'));
        $this->_addTranslation('You can change the number of gift registry items on the Gift Registry Info page or directly in your cart, but not while in checkout.', __('You can change the number of gift registry items on the Gift Registry Info page or directly in your cart, but not while in checkout.'));
        $this->_addTranslation('No confirmation', __('No confirmation'));
        $this->_addTranslation('Sorry, something went wrong.', __('Sorry, something went wrong.'));
        $this->_addTranslation('Sorry, something went wrong. Please try again later.', __('Sorry, something went wrong. Please try again later.'));
        $this->_addTranslation('select all', __('select all'));
        $this->_addTranslation('unselect all', __('unselect all'));
        $this->_addTranslation('Please agree to all Terms and Conditions before placing the orders.', __('Please agree to all Terms and Conditions before placing the orders.'));
        $this->_addTranslation('Please choose to register or to checkout as a guest', __('Please choose to register or to checkout as a guest'));
        $this->_addTranslation('Your order cannot be completed at this time as there is no shipping methods available for it. Please make necessary changes in your shipping address.', __('Your order cannot be completed at this time as there is no shipping methods available for it. Please make necessary changes in your shipping address.'));
        $this->_addTranslation('Please specify shipping method.', __('Please specify shipping method.'));
        $this->_addTranslation('Your order cannot be completed at this time as there is no payment methods available for it.', __('Your order cannot be completed at this time as there is no payment methods available for it.'));
        // @codingStandardsIgnoreEnd
    }

    /**
     * Adds some translated text to the translated data array as long as the key and text don't match.
     *
     * There is no point in having translated text added if the key is already representing the translated text.
     *
     * @param string $key
     * @param string $translatedText
     * @return void
     */
    protected function _addTranslation($key, $translatedText)
    {
        if ($key !== $translatedText) {
            $this->_translateData[$key] = $translatedText;
        }
    }

}
