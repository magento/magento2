<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\ResourceModel\Entity\Type;

/**
 * Eav Resource Entity Type Collection Model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource initialization
     *
     * @return void
     * @codeCoverageIgnore
     */
    protected function _construct()
    {
        $this->_init(\Magento\Eav\Model\Entity\Type::class, \Magento\Eav\Model\ResourceModel\Entity\Type::class);
    }
}
