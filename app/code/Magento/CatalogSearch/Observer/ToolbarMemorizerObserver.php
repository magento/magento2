<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Observer;

use Magento\Catalog\Model\Product\ProductList\ToolbarMemorizer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ToolbarMemorizerObserver implements ObserverInterface
{
    /**
     * @var ToolbarMemorizer
     */
    private $toolbarMemorizer;

    /**
     * ToolbarMemoriserObserver constructor.
     * @param ToolbarMemorizer $toolbarMemorizer
     */
    public function __construct(ToolbarMemorizer $toolbarMemorizer)
    {
        $this->toolbarMemorizer = $toolbarMemorizer;
    }

    /**
     * Save toolbar parameters in catalog session
     *
     * @param Observer $observer
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Observer $observer): void
    {
        $this->toolbarMemorizer->memorizeParams();
    }
}
