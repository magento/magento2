<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\Declaration\Schema\Diff;

use Magento\Framework\ObjectManagerInterface;

/**
 * @api
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
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create Diff object.
     *
     * @param array $data
     * @return Diff
     */
    public function create(array $data)
    {
        return $this->objectManager->create(Diff::class, $data);
    }
}
