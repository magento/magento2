<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Ui\Component\Listing\Column;

use Magento\Directory\Model\ResourceModel\RegionFactory as RegionResourceFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Prepare grid column region.
 */
class Region extends Column
{
    /**
     * @var RegionResourceFactory
     */
    private $regionResourceFactory;

    /**
     * @var RegionFactory
     */
    private $regionFactory;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param RegionFactory $regionFactory
     * @param RegionResourceFactory $regionResourceFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        RegionFactory $regionFactory,
        RegionResourceFactory $regionResourceFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);

        $this->regionFactory = $regionFactory;
        $this->regionResourceFactory = $regionResourceFactory;
    }

    /**
     * @inheritdoc
     */
    public function prepareDataSource(array $dataSource)
    {
        if ($dataSource['data']['totalRecords'] > 0) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['region_id']) && $item['region_id'] != 0) {
                    $region = $this->regionFactory->create();
                    $regionResource = $this->regionResourceFactory->create();
                    $regionResource->load($region, $item['region_id']);

                    $item['region'] = $region->getName();
                }
            }
        }

        return $dataSource;
    }
}
