<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Ui\Component\Listing\Column;

use Magento\InventorySales\Ui\SalesChannelNameResolver;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Add grid column for sales channels
 */
class SalesChannels extends Column
{
    /**
     * @var SalesChannelNameResolver
     */
    private $salesChannelNameResolver;

    /**
     * SalesChannels constructor.
     * @param SalesChannelNameResolver $salesChannelNameResolver
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        SalesChannelNameResolver $salesChannelNameResolver,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->salesChannelNameResolver = $salesChannelNameResolver;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare column value
     *
     * @param array $salesChannelData
     * @return array
     */
    private function prepareSalesChannelData(array $salesChannelData): array
    {
        $refactoredChannelData = [];
        foreach ($salesChannelData as $type => $salesChannel) {
            foreach ($salesChannel as $key => $code) {
                $refactoredChannelData[$type][$key]['name'] = $this->salesChannelNameResolver->resolve($type, $code);
                $refactoredChannelData[$type][$key]['code'] = $code;
            }
        }
        return $refactoredChannelData;
    }

    /**
     * Prepare data source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if ($dataSource['data']['totalRecords'] > 0) {
            foreach ($dataSource['data']['items'] as &$row) {
                $row['sales_channels'] = $this->prepareSalesChannelData($row['sales_channels']);
            }
        }
        unset($row);

        return $dataSource;
    }
}
