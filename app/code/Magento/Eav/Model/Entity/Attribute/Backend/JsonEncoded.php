<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Entity\Attribute\Backend;

use Magento\Framework\Serialize\Serializer\Json;

/**
 * Backend model for attribute that stores structures in json format
 *
 * @api
 * @since 100.2.0
 */
class JsonEncoded extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * ArrayBackend constructor.
     *
     * @param Json $jsonSerializer
     */
    public function __construct(Json $jsonSerializer)
    {
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * Encode before saving
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     * @since 100.2.0
     */
    public function beforeSave($object)
    {
        // parent::beforeSave() is not called intentionally
        $attrCode = $this->getAttribute()->getAttributeCode();
        if ($object->hasData($attrCode) && !$this->isJsonEncoded($object->getData($attrCode))) {
            $object->setData($attrCode, $this->jsonSerializer->serialize($object->getData($attrCode)));
        }
        return $this;
    }

    /**
     * Decode after loading
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     * @since 100.2.0
     */
    public function afterLoad($object)
    {
        parent::afterLoad($object);
        $attrCode = $this->getAttribute()->getAttributeCode();
        $object->setData($attrCode, $this->jsonSerializer->unserialize($object->getData($attrCode) ?: '{}'));
        return $this;
    }

    /**
     * Returns true if given param is a valid json value else false.
     *
     * @param string|int|float|bool|array|null $value
     * @return bool
     */
    private function isJsonEncoded($value): bool
    {
        $result = is_string($value);
        if ($result) {
            try {
                $this->jsonSerializer->unserialize($value);
            } catch (\InvalidArgumentException $e) {
                $result = false;
            }
        }

        return $result;
    }
}
