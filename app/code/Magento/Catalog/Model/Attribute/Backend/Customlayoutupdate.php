<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Attribute\Backend;

use Magento\Catalog\Model\AbstractModel;
use Magento\Catalog\Model\Category;
use Magento\Framework\Exception\LocalizedException;
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
     * @param \Magento\Framework\DataObject $object
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

        /** @var $validator \Magento\Framework\View\Model\Layout\Update\Validator */
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
     * @inheritDoc
     * @param AbstractModel $object
     * @throws LocalizedException
     */
    public function beforeSave($object)
    {
        $attributeName = $this->getAttribute()->getName();
        if ($object->getData($attributeName)
            && $object->getOrigData($attributeName) !== $object->getData($attributeName)
        ) {
            throw new LocalizedException(__('Custom layout update text cannot be changed, only removed'));
        }

        return parent::beforeSave($object);
    }
}
