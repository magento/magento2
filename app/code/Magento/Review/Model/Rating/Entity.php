<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Model\Rating;

/**
 * Ratings entity model
 *
 * @method string getEntityCode()
 * @method \Magento\Review\Model\Rating\Entity setEntityCode(string $value)
 *
 * @codeCoverageIgnore
 */
class Entity extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialise the model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Review\Model\ResourceModel\Rating\Entity::class);
    }

    /**
     * Return the ID for the specified code
     *
     * @param string $entityCode
     * @return int
     */
    public function getIdByCode($entityCode)
    {
        return $this->_getResource()->getIdByCode($entityCode);
    }
}
