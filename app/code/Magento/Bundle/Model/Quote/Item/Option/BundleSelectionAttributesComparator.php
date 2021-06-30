<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Model\Quote\Item\Option;

use Magento\Framework\DataObject;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Model\Quote\Item\Option\ComparatorInterface;

/**
 * Bundle quote item option comparator
 */
class BundleSelectionAttributesComparator implements ComparatorInterface
{
    /**
     * @var Json
     */
    private $serializer;

    /**
     * @param Json $serializer
     */
    public function __construct(
        Json $serializer
    ) {
        $this->serializer = $serializer;
    }

    /**
     * @inheritdoc
     */
    public function compare(DataObject $option1, DataObject $option2): bool
    {
        $value1 = $option1->getValue() ? $this->serializer->unserialize($option1->getValue()) : [];
        $value2 = $option2->getValue() ? $this->serializer->unserialize($option2->getValue()) : [];
        $option1Id = isset($value1['option_id']) ? (int) $value1['option_id'] : null;
        $option2Id = isset($value2['option_id']) ? (int) $value2['option_id'] : null;

        return $option1Id === $option2Id;
    }
}
