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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\CheckoutAgreements\Model;

/**
 * @method \Magento\CheckoutAgreements\Model\Resource\Agreement _getResource()
 * @method \Magento\CheckoutAgreements\Model\Resource\Agreement getResource()
 * @method string getName()
 * @method \Magento\CheckoutAgreements\Model\Agreement setName(string $value)
 * @method string getContent()
 * @method \Magento\CheckoutAgreements\Model\Agreement setContent(string $value)
 * @method string getContentHeight()
 * @method \Magento\CheckoutAgreements\Model\Agreement setContentHeight(string $value)
 * @method string getCheckboxText()
 * @method \Magento\CheckoutAgreements\Model\Agreement setCheckboxText(string $value)
 * @method int getIsActive()
 * @method \Magento\CheckoutAgreements\Model\Agreement setIsActive(int $value)
 * @method int getIsHtml()
 * @method \Magento\CheckoutAgreements\Model\Agreement setIsHtml(int $value)
 *
 */
class Agreement extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Allowed CSS units for height field
     *
     * @var array
     */
    protected $allowedCssUnits = array('px', 'pc', 'pt', 'ex', 'em', 'mm', 'cm', 'in', '%');

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\CheckoutAgreements\Model\Resource\Agreement');
    }

    /**
     * @param \Magento\Framework\Object $agreementData
     * @return array|bool
     */
    public function validateData($agreementData)
    {
        $errors = [];

        $contentHeight = $agreementData->getContentHeight();
        if ($contentHeight !== ''
            && !preg_match('/^[0-9]*\.*[0-9]+(' . implode("|", $this->allowedCssUnits) . ')?$/', $contentHeight)
        ) {
            $errors[] = "Please input a valid CSS-height. For example 100px or 77pt or 20em or .5ex or 50%.";
        }

        return (count($errors)) ? $errors : true;
    }

    /**
     * Processing object before save data
     *
     * @return $this
     */
    protected function _beforeSave()
    {
        if ($this->getContentHeight() == 0) {
            $this->setContentHeight(''); //converting zero Content-Height
        }

        if ($this->getContentHeight()
            && !preg_match('/('. implode("|", $this->allowedCssUnits) . ')/', $this->getContentHeight())
        ) {
            $contentHeight = $this->getContentHeight() . 'px'; //setting default units for Content-Height
            $this->setContentHeight($contentHeight);
        }

        return parent::_beforeSave();
    }
}
