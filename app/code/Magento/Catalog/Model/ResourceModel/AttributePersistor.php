<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\ResourceModel;

use Magento\Framework\Model\Entity\ScopeInterface;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as CatalogEavAttribute;

class AttributePersistor extends \Magento\Eav\Model\ResourceModel\AttributePersistor
{
    /**
     * @param ScopeInterface $scope
     * @param AbstractAttribute $attribute
     * @param bool $useDefault
     * @return string
     */
    protected function getScopeValue(ScopeInterface $scope, AbstractAttribute $attribute, $useDefault = false)
    {
        if ($attribute instanceof CatalogEavAttribute) {
            $useDefault = $useDefault || $attribute->isScopeGlobal();
        }
        return parent::getScopeValue($scope, $attribute, $useDefault);
    }
}
