<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LayeredNavigation\Observer\Edit\Tab\Front;

use Magento\Framework\Module\Manager;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\View\Element\BlockInterface;

class ProductAttributeFormBuildFormFieldDependenciesObserver implements ObserverInterface
{
    /**
     * @var Manager
     */
    protected Manager $moduleManager;

    /**
     * @param Manager $moduleManager
     */
    public function __construct(Manager $moduleManager)
    {
        $this->moduleManager = $moduleManager;
    }

    /**
     * Adds field related dependencies in the administrator attribute edit form
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        if (!$this->moduleManager->isOutputEnabled('Magento_LayeredNavigation')) {
            return;
        }
        /** @var BlockInterface $dependencies */
        $dependencies = $observer->getDependencies();
        $dependencies->addFieldMap('is_filterable_in_search', 'filterable_in_search');
        $dependencies->addFieldDependence(
            'filterable_in_search',
            'searchable',
            '1'
        );
    }
}
