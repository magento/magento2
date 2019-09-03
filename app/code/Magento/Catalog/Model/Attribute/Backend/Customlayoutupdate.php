<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Attribute\Backend;

use Magento\Catalog\Model\AbstractModel;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Model\Layout\Update\ValidatorFactory;
use Magento\Eav\Model\Entity\Attribute\Exception;
use Magento\Catalog\Model\Category\Attribute\Backend\LayoutUpdate as CategoryLayoutUpdate;
use Magento\Catalog\Model\Product\Attribute\Backend\LayoutUpdate as ProductLayoutUpdate;

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
     * Extract an attribute value.
     *
     * @param AbstractModel $object
     * @param string|null $attributeCode
     * @return mixed
     */
    private function extractValue(AbstractModel $object, ?string $attributeCode = null)
    {
        $attributeCode = $attributeCode ?? $this->getAttribute()->getName();
        $attribute = $object->getCustomAttribute($attributeCode);

        return $object->getData($attributeCode) ?? ($attribute ? $attribute->getValue() : null);
    }

    /**
     * Put an attribute value.
     *
     * @param AbstractModel $object
     * @param mixed $value
     * @param string|null $attributeCode
     * @return void
     */
    private function putValue(AbstractModel $object, $value, ?string $attributeCode = null): void
    {
        $attributeCode = $attributeCode ?? $this->getAttribute()->getName();
        $object->setCustomAttribute($attributeCode, $value);
        $object->setData($attributeCode, $value);
    }

    /**
     * @inheritDoc
     * @param AbstractModel $object
     * @throws LocalizedException
     */
    public function beforeSave($object)
    {
        $attributeName = $this->getAttribute()->getName();
        $value = $this->extractValue($object);
        //New values are not accepted
        if ($value && $object->getOrigData($attributeName) !== $value) {
            throw new LocalizedException(__('Custom layout update text cannot be changed, only removed'));
        }
        //If custom file was selected we need to remove this attribute
        $file = $this->extractValue($object, 'custom_layout_update_file');
        if ($file
            && $file !== CategoryLayoutUpdate::VALUE_USE_UPDATE_XML
            && $file !== ProductLayoutUpdate::VALUE_USE_UPDATE_XML
        ) {
            $this->putValue($object, null);
        }

        return parent::beforeSave($object);
    }
}
