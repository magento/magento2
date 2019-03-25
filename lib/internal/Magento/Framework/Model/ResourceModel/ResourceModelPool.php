<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\ResourceModel;

use Magento\Framework\ObjectManagerInterface;

/**
 * Pool of resource model instances per entity
 */
class ResourceModelPool implements ResourceModelPoolInterface
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @inheritdoc
     */
    public function get(string $resourceClassName): AbstractResource
    {
        return $this->objectManager->get($resourceClassName);
    }
}
