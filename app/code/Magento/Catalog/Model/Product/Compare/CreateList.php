<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Compare;

use Magento\Catalog\Model\CompareListFactory;
use Magento\Framework\Math\Random;

/**
 * Create new compare list.
 */
class CreateList
{
    /**
     * @var CompareListFactory
     */
    private $compareListFactory;

    /**
     * @var Random
     */
    private $randomDataGenerator;

    /**
     * @param CompareListFactory $compareListFactory
     * @param Random $randomDataGenerator
     */
    public function __construct(
        CompareListFactory $compareListFactory,
        Random $randomDataGenerator
    ) {
        $this->compareListFactory = $compareListFactory;
        $this->randomDataGenerator = $randomDataGenerator;
    }

    /**
     * If customerId === 0 list will be created for guest.
     *
     * @param int $customerId
     */
    public function execute(int $customerId)
    {
        $compareList = $this->compareListFactory->create();

        if (0 !== $customerId) {
            $compareList->setCustomerId($customerId);
        }

        $compareList->setHashedId($this->randomDataGenerator->getUniqueHash())->save();

        return $compareList;
    }
}
