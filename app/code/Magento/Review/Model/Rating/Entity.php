<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Review\Model\Rating;

/**
 * Ratings entity model
 *
 * @method string getEntityCode()
 * @method \Magento\Review\Model\Rating\Entity setEntityCode(string $value)
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @codeCoverageIgnore
 */
class Entity extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(\Magento\Review\Model\ResourceModel\Rating\Entity::class);
    }

    /**
     * @param string $entityCode
     * @return int
     */
    public function getIdByCode(string $entityCode): int
    {
        return $this->_getResource()->getIdByCode($entityCode);
    }
}
