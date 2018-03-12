<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\InventoryCatalog\Model\IsSingleSourceModeInterface;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Check if single stock system and if false not show this column.
 */
class Quantity extends Column
{
    /**
     * @var IsSingleSourceModeInterface
     */
    private $isSingleSourceMode;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param IsSingleSourceModeInterface $isSingleSourceMode
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        IsSingleSourceModeInterface $isSingleSourceMode,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->isSingleSourceMode = $isSingleSourceMode;
    }

    /**
     * @inheritdoc
     */
    public function prepare()
    {
        if ($this->isSingleSourceMode->execute() === false) {
            $this->unsetData();
        } else {
            parent::prepare();
        }
    }
}
