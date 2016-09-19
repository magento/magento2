<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\EntityManager;

/**
 * Class CustomAttributesMapper
 */
class CustomAttributesMapper implements MapperInterface
{
    /**
     * {@inheritdoc}
     * @deprecated
     */
    public function entityToDatabase($entityType, $data)
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     * @deprecated
     */
    public function databaseToEntity($entityType, $data)
    {
        return $data;
    }
}
