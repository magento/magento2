<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
    protected $allowedCssUnits = ['px', 'pc', 'pt', 'ex', 'em', 'mm', 'cm', 'in', '%'];

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
    public function beforeSave()
    {
        if ($this->getContentHeight() == 0) {
            $this->setContentHeight(''); //converting zero Content-Height
        }

        if ($this->getContentHeight()
            && !preg_match('/(' . implode("|", $this->allowedCssUnits) . ')/', $this->getContentHeight())
        ) {
            $contentHeight = $this->getContentHeight() . 'px'; //setting default units for Content-Height
            $this->setContentHeight($contentHeight);
        }

        return parent::beforeSave();
    }
}
