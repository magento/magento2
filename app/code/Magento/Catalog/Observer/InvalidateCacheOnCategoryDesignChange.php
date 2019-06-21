<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Observer for invalidating cache on catalog category design change
 */
class InvalidateCacheOnCategoryDesignChange implements ObserverInterface
{
    /**
     * @var array
     */
    private $designAttributes = [
        'custom_design',
        'page_layout',
        'custom_layout_update',
        'custom_apply_to_products',
        'custom_use_parent_settings'
    ];

    /**
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     */
    public function __construct(\Magento\Framework\App\Cache\TypeListInterface $cacheTypeList)
    {
        $this->cacheTypeList = $cacheTypeList;
    }

    /**
     * Invalidate cache on category design attribute value changed
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(Observer $observer)
    {
        $category = $observer->getEvent()->getEntity();
        if (!$category->isObjectNew()) {
            foreach ($this->designAttributes as $designAttribute) {
                if ($category->dataHasChangedFor($designAttribute)) {
                    $this->cacheTypeList->invalidate(
                        [
                            \Magento\PageCache\Model\Cache\Type::TYPE_IDENTIFIER,
                            \Magento\Framework\App\Cache\Type\Layout::TYPE_IDENTIFIER
                        ]
                    );
                    break;
                }
            }
        }
    }
}
