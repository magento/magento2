<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Model\Rule\Action;

/**
 * @api
 * @since 2.0.0
 */
class Collection extends \Magento\Rule\Model\Action\Collection
{
    /**
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param \Magento\Rule\Model\ActionFactory $actionFactory
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Rule\Model\ActionFactory $actionFactory,
        array $data = []
    ) {
        parent::__construct($assetRepo, $layout, $actionFactory, $data);
        $this->setType(\Magento\CatalogRule\Model\Rule\Action\Collection::class);
    }

    /**
     * @return array
     * @since 2.0.0
     */
    public function getNewChildSelectOptions()
    {
        $actions = parent::getNewChildSelectOptions();
        $actions = array_merge_recursive(
            $actions,
            [
                ['value' => \Magento\CatalogRule\Model\Rule\Action\Product::class, 'label' => __('Update the Product')]
            ]
        );
        return $actions;
    }
}
