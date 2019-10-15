<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Attribute\Source;

use Magento\Catalog\Model\Attribute\Source\AbstractLayoutUpdate;
use Magento\Catalog\Model\Product\Attribute\LayoutUpdateManager;
use Magento\Framework\Api\CustomAttributesDataInterface;

/**
 * List of layout updates available for a product.
 */
class LayoutUpdate extends AbstractLayoutUpdate
{
    /**
     * @var LayoutUpdateManager
     */
    private $manager;

    /**
     * @param LayoutUpdateManager $manager
     */
    public function __construct(LayoutUpdateManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @inheritDoc
     */
    protected function listAvailableOptions(CustomAttributesDataInterface $entity): array
    {
        return $this->manager->fetchAvailableFiles($entity);
    }
}
