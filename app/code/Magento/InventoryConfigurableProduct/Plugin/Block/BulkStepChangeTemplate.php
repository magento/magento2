<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurableProduct\Plugin\Block;

use Magento\ConfigurableProduct\Block\Adminhtml\Product\Steps\Bulk;
use Magento\InventoryCatalog\Model\IsSingleSourceModeInterface;

class BulkStepChangeTemplate
{
    /**
     * @var IsSingleSourceModeInterface
     */
    private $isSingleSourceMode;

    /**
     * @param IsSingleSourceModeInterface $isSingleSourceMode
     */
    public function __construct(
        IsSingleSourceModeInterface $isSingleSourceMode
    ) {
        $this->isSingleSourceMode = $isSingleSourceMode;
    }

    /**
     * @param Bulk $bulk
     * @param string $template
     *
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSetTemplate(Bulk $bulk, string $template)
    {
        if ($this->isSingleSourceMode->execute() === false) {
            $template = 'Magento_InventoryConfigurableProduct::catalog/product/edit/attribute/steps/bulk.phtml';
        }

        return $template;
    }
}
