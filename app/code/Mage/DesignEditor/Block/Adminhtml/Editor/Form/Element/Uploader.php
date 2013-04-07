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
 * @package     Mage_DesignEditor
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Form element renderer to display file element for VDE
 *
 * @method Mage_DesignEditor_Block_Adminhtml_Editor_Form_Element_Uploader setAccept($accept)
 * @method Mage_DesignEditor_Block_Adminhtml_Editor_Form_Element_Uploader setMultiple(bool $isMultiple)
 */
class Mage_DesignEditor_Block_Adminhtml_Editor_Form_Element_Uploader extends Varien_Data_Form_Element_File
{
    //const CONTROL_TYPE = 'uploader';

    /**
     * Additional html attributes
     *
     * @var array
     */
    protected $_htmlAttributes = array('accept', 'multiple');

    /**
     * Html attributes
     *
     * @return array
     */
    public function getHtmlAttributes()
    {
        $attributes = parent::getHtmlAttributes();
        return array_merge($attributes, $this->_htmlAttributes);
    }
}
