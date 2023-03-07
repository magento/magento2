<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Observer;

use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Weee\Model\Attribute\Backend\Weee\Tax;

class AssignBackendModelToAttributeObserver implements ObserverInterface
{
    /**
     * @param Type $productType
     * @param ConfigInterface $productTypeConfig
     */
    public function __construct(
        protected Type $productType,
        protected ConfigInterface $productTypeConfig
    ) {
    }

    /**
     * Automatically assign backend model to weee attributes
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $backendModel = Tax::getBackendModelName();
        /** @var $object AbstractAttribute */
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
