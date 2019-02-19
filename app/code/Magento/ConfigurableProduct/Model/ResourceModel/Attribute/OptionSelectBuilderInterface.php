<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\ResourceModel\Attribute;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\ScopeInterface;
use Magento\Framework\DB\Select;

/**
 * Interface to build select for retrieving configurable options.
 */
interface OptionSelectBuilderInterface
{
    /**
     * Get load options for attribute select.
     *
     * @param AbstractAttribute $superAttribute
     * @param int $productId
     * @param ScopeInterface $scope
     * @return Select
     */
    public function getSelect(AbstractAttribute $superAttribute, int $productId, ScopeInterface $scope);
}
