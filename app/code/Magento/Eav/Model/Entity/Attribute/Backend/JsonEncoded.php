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
        if ($object->hasData($attrCode)) {
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
}
