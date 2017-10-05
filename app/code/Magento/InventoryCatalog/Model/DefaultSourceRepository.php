<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InventoryCatalog\Model;

use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryCatalog\Api\DefaultSourceRepositoryInterface;
use Magento\Inventory\Model\Source\Command\GetInterface;

/**
 * Class DefaultSourceRepository
 */
class DefaultSourceRepository implements DefaultSourceRepositoryInterface
{

    /**
     * @var GetInterface
     */
    private $commandGet;

    /**
     * @param GetInterface $commandGet
     */
    public function __construct(GetInterface $commandGet)
    {
        $this->commandGet = $commandGet;
    }

    /**
     * Get default stock
     *
     * @return SourceInterface
     */
    public function get(): SourceInterface
    {
        return $this->commandGet->execute(DefaultSourceRepositoryInterface::DEFAULT_SOURCE);
    }
}
