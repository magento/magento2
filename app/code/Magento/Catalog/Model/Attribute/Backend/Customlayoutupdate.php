<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Attribute\Backend;

use Magento\Framework\Config\Dom\ValidationException;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Model\Layout\Update\Validator;
use Magento\Framework\View\Model\Layout\Update\ValidatorFactory;
use Magento\Eav\Model\Entity\Attribute\Exception;

/**
 * Layout update attribute backend
 *
 * @api
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 * @since 100.0.2
 */
class Customlayoutupdate extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * Layout update validator factory
     *
     * @var ValidatorFactory
     */
    protected $_layoutUpdateValidatorFactory;

    /**
     * Construct the custom layout update class
     *
     * @param ValidatorFactory $layoutUpdateValidatorFactory
     */
    public function __construct(ValidatorFactory $layoutUpdateValidatorFactory)
    {
        $this->_layoutUpdateValidatorFactory = $layoutUpdateValidatorFactory;
    }

    /**
     * Validate the custom layout update
     *
     * @param DataObject $object
     * @return bool
     * @throws Exception
     */
    public function validate($object)
    {
        $attributeName = $this->getAttribute()->getName();
        $xml = trim($object->getData($attributeName));

        if (!$this->getAttribute()->getIsRequired() && empty($xml)) {
            return true;
        }

        /** @var Validator $validator */
        $validator = $this->_layoutUpdateValidatorFactory->create();
        if (!$validator->isValid($xml)) {
            $messages = $validator->getMessages();
            //Add first message to exception
            $message = array_shift($messages);
            $eavExc = new Exception(__($message));
            $eavExc->setAttributeCode($attributeName);
            throw $eavExc;
        }

        return true;
    }

    /**
     * Attribute before save method.
     *
     * @param DataObject $object
     * @return $this
     * @throws LocalizedException
     * @since 103.0.3
     */
    public function beforeSave($object)
    {
        parent::beforeSave($object);

        $attributeLabel = $this->getAttribute()->getData('frontend_label');

        try {
            $this->validate($object);
        } catch (ValidationException $e) {
            throw new LocalizedException(__('%1 is invalid: %2', $attributeLabel, $e->getMessage()));
        }

        return $this;
    }
}
