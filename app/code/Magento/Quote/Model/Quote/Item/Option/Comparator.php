<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\Quote\Item\Option;

use InvalidArgumentException;
use Magento\Framework\DataObject;

/**
 * Quote item options comparator
 */
class Comparator implements ComparatorInterface
{
    /**
     * @var ComparatorInterface[]
     */
    private $customComparators;

    /**
     * @param ComparatorInterface[] $customComparators
     */
    public function __construct(
        array $customComparators = []
    ) {
        foreach ($customComparators as $comparator) {
            if (!$comparator instanceof ComparatorInterface) {
                throw new InvalidArgumentException(
                    sprintf(
                        '%s must implement %s',
                        get_class($comparator),
                        ComparatorInterface::class
                    )
                );
            }
        }
        $this->customComparators = $customComparators;
    }

    /**
     * @inheritdoc
     */
    public function compare(
        DataObject $option1,
        DataObject $option2
    ): bool {
        if ($option1->getCode() === $option2->getCode()) {
            return isset($this->customComparators[$option1->getCode()])
                ? $this->customComparators[$option1->getCode()]->compare($option1, $option2)
                : $option1->getValue() == $option2->getValue();
        }

        return false;
    }
}
