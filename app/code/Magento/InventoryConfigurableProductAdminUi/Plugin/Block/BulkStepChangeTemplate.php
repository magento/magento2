<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurableProductAdminUi\Plugin\Block;

use Magento\ConfigurableProduct\Block\Adminhtml\Product\Steps\Bulk;
use Magento\InventoryCatalogApi\Model\IsSingleSourceModeInterface;

/**
 * Change template if not single store mode.
 */
class BulkStepChangeTemplate
{
    /**
     * @var IsSingleSourceModeInterface
     */
    private $isSingleSourceMode;

    /**
     * @var string
     */
    private $multiSourceTemplate;

    /**
     * @param IsSingleSourceModeInterface $isSingleSourceMode
     * @param string $multiSourceTemplate
     */
    public function __construct(
        IsSingleSourceModeInterface $isSingleSourceMode,
        string $multiSourceTemplate
    ) {
        $this->isSingleSourceMode = $isSingleSourceMode;
        $this->multiSourceTemplate = $multiSourceTemplate;
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
            $template = $this->multiSourceTemplate;
        }

        return $template;
    }
}
