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
 * @package     Mage_Page
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Customer Redirect Page
 *
 * @category    Mage
 * @package     Mage_Page
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Page_Block_Redirect extends Mage_Core_Block_Template
{

    /**
     *  HTML form hidden fields
     */
    protected $_formFields = array();

    /**
     *  URL for redirect location
     *
     *  @return	  string URL
     */
    public function getTargetURL ()
    {
        return '';
    }

    /**
     *  Additional custom message
     *
     *  @return	  string Output message
     */
    public function getMessage ()
    {
        return '';
    }

    /**
     *  Client-side redirect engine output
     *
     *  @return	  string
     */
    public function getRedirectOutput ()
    {
        if ($this->isHtmlFormRedirect()) {
            return $this->getHtmlFormRedirect();
        } else {
            return $this->getJsRedirect();
        }
    }

    /**
     *  Redirect via JS location
     *
     *  @return	  string
     */
    public function getJsRedirect ()
    {
        $js  = '<script type="text/javascript">';
        $js .= 'document.location.href="' . $this->getTargetURL() . '";';
        $js .= '</script>';
        return $js;
    }

    /**
     *  Redirect via HTML form submission
     *
     *  @return	  string
     */
    public function getHtmlFormRedirect ()
    {
        $form = new Varien_Data_Form();
        $form->setAction($this->getTargetURL())
            ->setId($this->getFormId())
            ->setName($this->getFormId())
            ->setMethod($this->getMethod())
            ->setUseContainer(true);
        foreach ($this->_getFormFields() as $field => $value) {
            $form->addField($field, 'hidden', array('name' => $field, 'value' => $value));
        }
        $html = $form->toHtml();
        $html.= '<script type="text/javascript">document.getElementById("' . $this->getFormId() . '").submit();</script>';
        return $html;
    }

    /**
     *  HTML form or JS redirect
     *
     *  @return	  boolean
     */
    public function isHtmlFormRedirect ()
    {
        return is_array($this->_getFormFields()) && count($this->_getFormFields()) > 0;
    }

    /**
     *  HTML form id/name attributes
     *
     *  @return	  string Id/name
     */
    public function getFormId()
    {
        return '';
    }

    /**
     *  HTML form method attribute
     *
     *  @return	  string Method
     */
    public function getFormMethod ()
    {
        return 'POST';
    }

    /**
     *  Array of hidden form fields (name => value)
     *
     *  @return	  array
     */
    public function getFormFields()
    {
        return array();
    }

    /**
     *  Optimized getFormFields() method
     *
     *  @return	  array
     */
    protected function _getFormFields()
    {
        if (!is_array($this->_formFields) || count($this->_formFields) == 0) {
            $this->_formFields = $this->getFormFields();
        }
        return $this->_formFields;
    }

}
