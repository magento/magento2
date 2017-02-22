<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Entity\Attribute\Backend\Time;

/**
 * Entity/Attribute/Model - attribute backend default
 */
class Created extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @codeCoverageIgnore
     */
    public function __construct(\Magento\Framework\Stdlib\DateTime\DateTime $dateTime)
    {
        $this->dateTime = $dateTime;
    }

    /**
     * Set created date
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    public function beforeSave($object)
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();
        if ($object->isObjectNew() && $object->getData($attributeCode) === null) {
            //$object->setData($attributeCode, $this->dateTime->gmtDate());
            $object->setData($attributeCode, gmdate('Y-m-d H:i:s'));
        }

        return $this;
    }
}
