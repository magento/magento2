<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Api\Data\ProductInterface;

/**
 * @api
 * @since 2.1.0
 */
class StatusText extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Source\Status
     * @since 2.1.0
     */
    protected $status;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Status $status
     * @param array $components
     * @param array $data
     * @since 2.1.0
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Status $status,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);

        $this->status = $status;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     * @since 2.1.0
     */
    public function prepareDataSource(array $dataSource)
    {
        $dataSource = parent::prepareDataSource($dataSource);

        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }

        $fieldName = $this->getData('name');
        $sourceFieldName = ProductInterface::STATUS;

        foreach ($dataSource['data']['items'] as &$item) {
            if (!empty($item[$sourceFieldName])) {
                $item[$fieldName] = $this->status->getOptionText($item[$sourceFieldName]);
            }
        }

        return $dataSource;
    }
}
