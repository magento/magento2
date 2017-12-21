<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Diff;

use Magento\Framework\ObjectManagerInterface;
use Zend\Di\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

/**
 * @see DiffInterface
 */
class DiffFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * ChangeRegistryFactory constructor.
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @return Diff
     */
    public function create()
    {
        return $this->objectManager->create(Diff::class);
    }
}
