<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Model\Category\Attribute\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Eav\Model\Entity\Attribute\Source\SpecificSourceInterface;
use Magento\Framework\Api\CustomAttributesDataInterface;

/**
 * List of layout updates available for a category.
 */
class LayoutUpdate extends AbstractSource implements SpecificSourceInterface
{
    /**
     * @inheritDoc
     */
    public function getAllOptions()
    {
        $options = [['label' => 'Use default', 'value' => '']];

        return $options;
    }

    /**
     * @inheritDoc
     */
    public function getOptionsFor(CustomAttributesDataInterface $entity): array
    {
        return $this->getAllOptions();
    }
}
