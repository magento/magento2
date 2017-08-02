<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Model;

use Magento\CheckoutAgreements\Api\Data\AgreementInterface;

/**
 * Class \Magento\CheckoutAgreements\Model\Agreement
 *
 * @since 2.0.0
 */
class Agreement extends \Magento\Framework\Model\AbstractExtensibleModel implements AgreementInterface
{
    /**
     * Allowed CSS units for height field
     *
     * @var array
     * @since 2.0.0
     */
    protected $allowedCssUnits = ['px', 'pc', 'pt', 'ex', 'em', 'mm', 'cm', 'in', '%'];

    /**
     * @return void
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init(\Magento\CheckoutAgreements\Model\ResourceModel\Agreement::class);
    }

    /**
     * @param \Magento\Framework\DataObject $agreementData
     * @return array|bool
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getAgreementId()
    {
        return $this->getData(self::AGREEMENT_ID);
    }

    /**
     * @inheritdoc
     * @since 2.0.0
     */
    public function setAgreementId($id)
    {
        return $this->setData(self::AGREEMENT_ID, $id);
    }

    /**
     * @inheritdoc
     * @since 2.0.0
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * @inheritdoc
     * @since 2.0.0
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * @inheritdoc
     * @since 2.0.0
     */
    public function getContent()
    {
        return $this->getData(self::CONTENT);
    }

    /**
     * @inheritdoc
     * @since 2.0.0
     */
    public function setContent($content)
    {
        return $this->setData(self::CONTENT, $content);
    }

    /**
     * @inheritdoc
     * @since 2.0.0
     */
    public function getContentHeight()
    {
        return $this->getData(self::CONTENT_HEIGHT);
    }

    /**
     * @inheritdoc
     * @since 2.0.0
     */
    public function setContentHeight($height)
    {
        return $this->setData(self::CONTENT_HEIGHT, $height);
    }

    /**
     * @inheritdoc
     * @since 2.0.0
     */
    public function getCheckboxText()
    {
        return $this->getData(self::CHECKBOX_TEXT);
    }

    /**
     * @inheritdoc
     * @since 2.0.0
     */
    public function setCheckboxText($text)
    {
        return $this->setData(self::CHECKBOX_TEXT, $text);
    }

    /**
     * @inheritdoc
     * @since 2.0.0
     */
    public function getIsActive()
    {
        return $this->getData(self::IS_ACTIVE);
    }

    /**
     * @inheritdoc
     * @since 2.0.0
     */
    public function setIsActive($status)
    {
        return $this->setData(self::IS_ACTIVE, $status);
    }

    /**
     * @inheritdoc
     * @since 2.0.0
     */
    public function getIsHtml()
    {
        return $this->getData(self::IS_HTML);
    }

    /**
     * @inheritdoc
     * @since 2.0.0
     */
    public function setIsHtml($isHtml)
    {
        return $this->setData(self::IS_HTML, $isHtml);
    }

    /**
     * @inheritdoc
     * @since 2.0.0
     */
    public function getMode()
    {
        return $this->getData(self::MODE);
    }

    /**
     * @inheritdoc
     * @since 2.0.0
     */
    public function setMode($mode)
    {
        return $this->setData(self::MODE, $mode);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\CheckoutAgreements\Api\Data\AgreementExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\CheckoutAgreements\Api\Data\AgreementExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\CheckoutAgreements\Api\Data\AgreementExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    //@codeCoverageIgnoreEnd
}
