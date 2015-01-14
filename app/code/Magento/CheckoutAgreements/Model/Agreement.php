<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Model;

use Magento\CheckoutAgreements\Api\Data\AgreementInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

class Agreement extends AbstractExtensibleModel implements AgreementInterface
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

    //@codeCoverageIgnoreStart
    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getData('agreement_id');
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->getData('name');
    }

    /**
     * @inheritdoc
     */
    public function getContent()
    {
        return $this->getData('content');
    }

    /**
     * @inheritdoc
     */
    public function getContentHeight()
    {
        return $this->getData('content_height');
    }

    /**
     * @inheritdoc
     */
    public function getCheckboxText()
    {
        return $this->getData('checkbox_text');
    }

    /**
     * @inheritdoc
     */
    public function getIsActive()
    {
        return $this->getData('is_active');
    }

    /**
     * @inheritdoc
     */
    public function getIsHtml()
    {
        return $this->getData('is_html');
    }
    //@codeCoverageIgnoreEnd
}
