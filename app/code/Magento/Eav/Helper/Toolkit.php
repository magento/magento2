<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Helper;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Registry;

/**
 * Class Toolkit
 */
class Toolkit
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var array
     */
    protected $usedDefault = [];

    /**
     * @var array
     */
    protected $canDisplayUseDefault = [];

    /**
     * Toolkit constructor
     *
     * @param Registry $registry
     */
    public function __construct(
        Registry $registry
    ) {
        $this->registry = $registry;
    }

    /**
     * Whether attribute can have default value
     *
     * @param AbstractAttribute $attribute
     * @return bool
     */
    public function canDisplayUseDefault(AbstractAttribute $attribute)
    {
        $attributeCode = $attribute->getAttributeCode();

        if (isset($this->canDisplayUseDefault[$attributeCode])) {
            return $this->canDisplayUseDefault[$attributeCode];
        }

        return $this->canDisplayUseDefault[$attributeCode] = (
            !$attribute->isScopeGlobal() &&
            $this->getModel() &&
            $this->getModel()->getId() &&
            $this->getModel()->getStoreId()
        );
    }

    /**
     * Check default value usage fact
     *
     * @param AbstractAttribute $attribute
     * @return bool
     */
    public function usedDefault(AbstractAttribute $attribute)
    {
        $attributeCode = $attribute->getAttributeCode();
        $defaultValue = $this->getModel()->getAttributeDefaultValue($attributeCode);

        if (isset($this->usedDefault[$attributeCode])) {
            return $this->usedDefault[$attributeCode];
        }

        if (!$this->getModel()->getExistsStoreValueFlag($attributeCode)) {
            return true;
        } elseif ($this->getModel()->getData($attributeCode) == $defaultValue &&
            $this->getModel()->getStoreId() != $this->getDefaultStoreId()
        ) {
            return false;
        }
        if ($defaultValue === false && !$attribute->getIsRequired() && $this->getModel()->getData($attributeCode)) {
            return false;
        }

        return $this->usedDefault[$attributeCode] = ($defaultValue === false);
    }

    /**
     * @return ProductInterface
     */
    protected function getModel()
    {
        return $this->registry->registry('current_product');
    }

    /**
     * Default sore ID getter
     *
     * @return integer
     */
    protected function getDefaultStoreId()
    {
        return \Magento\Store\Model\Store::DEFAULT_STORE_ID;
    }
}
