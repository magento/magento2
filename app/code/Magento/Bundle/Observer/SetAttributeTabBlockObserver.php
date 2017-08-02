<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class \Magento\Bundle\Observer\SetAttributeTabBlockObserver
 *
 * @since 2.0.0
 */
class SetAttributeTabBlockObserver implements ObserverInterface
{
    /**
     * Catalog helper
     *
     * @var \Magento\Catalog\Helper\Catalog
     * @since 2.0.0
     */
    protected $helperCatalog;

    /**
     * @param \Magento\Catalog\Helper\Catalog $helperCatalog
     * @since 2.0.0
     */
    public function __construct(\Magento\Catalog\Helper\Catalog $helperCatalog)
    {
        $this->helperCatalog = $helperCatalog;
    }

    /**
     * Setting attribute tab block for bundle
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     * @since 2.0.0
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        if ($product->getTypeId() == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
            $this->helperCatalog->setAttributeTabBlock(
                \Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Attributes::class
            );
        }
        return $this;
    }
}
