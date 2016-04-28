<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\Entity;

/**
 * Class ScopeResolverInterface
 */
interface ScopeResolverInterface
{
    /**
     * @param string $entityType
     * @param array|null $entityData
     * @return \Magento\Framework\Model\Entity\ScopeInterface[]
     * @throws \Magento\Framework\Exception\ConfigurationMismatchException
     * @throws \Exception
     */
    public function getEntityContext($entityType, $entityData = []);
}
