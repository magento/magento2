<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class \Magento\Weee\Observer\AssignBackendModelToAttributeObserver
 *
 * @since 2.0.0
 */
class AssignBackendModelToAttributeObserver implements ObserverInterface
{
    /**
     * @var \Magento\Catalog\Model\Product\Type
     * @since 2.0.0
     */
    protected $productType;

    /**
     * @var \Magento\Catalog\Model\ProductTypes\ConfigInterface
     * @since 2.0.0
     */
    protected $productTypeConfig;

    /**
     * @param \Magento\Catalog\Model\Product\Type $productType
     * @param \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Catalog\Model\Product\Type $productType,
        \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig
    ) {
        $this->productType = $productType;
        $this->productTypeConfig = $productTypeConfig;
    }

    /**
     * Automaticaly assign backend model to weee attributes
     *
     * @param   \Magento\Framework\Event\Observer $observer
     * @return  $this
     * @since 2.0.0
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $backendModel = \Magento\Weee\Model\Attribute\Backend\Weee\Tax::getBackendModelName();
        /** @var $object \Magento\Eav\Model\Entity\Attribute\AbstractAttribute */
        $object = $observer->getEvent()->getAttribute();
        if ($object->getFrontendInput() == 'weee') {
            $object->setBackendModel($backendModel);
            if (!$object->getApplyTo()) {
                $applyTo = [];
                foreach ($this->productType->getOptions() as $option) {
                    if ($this->productTypeConfig->isProductSet($option['value'])) {
                        continue;
                    }
                    $applyTo[] = $option['value'];
                }
                $object->setApplyTo($applyTo);
            }
        }

        return $this;
    }
}
