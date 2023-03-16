<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Rule\Action;

use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\LayoutInterface;
use Magento\Rule\Model\Action\Collection as ActionCollection;
use Magento\Rule\Model\ActionFactory;
use Magento\SalesRule\Model\Rule\Action\Collection as RuleActionCollection;
use Magento\SalesRule\Model\Rule\Action\Product as RuleActionProduct;

class Collection extends ActionCollection
{
    /**
     * @param Repository $assetRepo
     * @param LayoutInterface $layout
     * @param ActionFactory $actionFactory
     * @param array $data
     */
    public function __construct(
        Repository $assetRepo,
        LayoutInterface $layout,
        ActionFactory $actionFactory,
        array $data = []
    ) {
        parent::__construct($assetRepo, $layout, $actionFactory, $data);
        $this->setType(RuleActionCollection::class);
    }

    /**
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $actions = parent::getNewChildSelectOptions();
        $actions = array_merge_recursive(
            $actions,
            [['value' => RuleActionProduct::class, 'label' => __('Update the Product')]]
        );
        return $actions;
    }
}
