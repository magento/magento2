<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Model\Query\Resolver;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;

class ContextFactory
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @return ContextInterface
     */
    public function create()
    {
        return $this->objectManager->create(ContextInterface::class);
    }
}
