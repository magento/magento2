<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryAdminUi\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\InventoryAdminUi\Model\OptionSource\RegionSource;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Prepare grid column region.
 */
class Region extends Column
{
    /**
     * @var RegionSource
     */
    private $regionSource;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param RegionSource $regionSource
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        RegionSource $regionSource,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->regionSource = $regionSource;
    }

    /**
     * @inheritdoc
     */
    public function prepareDataSource(array $dataSource)
    {
        if ($dataSource['data']['totalRecords'] > 0) {
            $options = array_column($this->regionSource->toOptionArray(), 'label', 'value');

            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['region_id'])) {
                    $item['region'] = $options[$item['region_id']] ?? '';
                }
            }
            unset($item);
        }
        return $dataSource;
    }
}
