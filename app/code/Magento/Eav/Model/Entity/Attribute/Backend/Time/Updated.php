<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Entity\Attribute\Backend\Time;

/**
 * Entity/Attribute/Model - attribute backend default
 * @api
 */
class Updated extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @codeCoverageIgnore
     * @deprecated 100.1.0 Remove unused dependency
     */
    public function __construct(\Magento\Framework\Stdlib\DateTime $dateTime)
    {
        $this->dateTime = $dateTime;
    }

    /**
     * Set modified date
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    public function beforeSave($object)
    {
        $object->setData(
            $this->getAttribute()->getAttributeCode(),
            (new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT)
        );
        return $this;
    }
}
