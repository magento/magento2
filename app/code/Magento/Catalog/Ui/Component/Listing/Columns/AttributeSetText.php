<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Eav\Api\AttributeSetRepositoryInterface;

class AttributeSetText extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * Column name
     */
    const NAME = 'attribute_set_id';

    /**
     * @var AttributeSetRepositoryInterface
     */
    protected $attributeSetRepository;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param AttributeSetRepositoryInterface $attributeSetRepository
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        AttributeSetRepositoryInterface $attributeSetRepository,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);

        $this->attributeSetRepository = $attributeSetRepository;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        $dataSource = parent::prepareDataSource($dataSource);

        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }

        $fieldName = $this->getData('name');

        foreach ($dataSource['data']['items'] as &$item) {
            if (!empty($item[static::NAME])) {
                $item[$fieldName] = $this->renderColumnText($item[static::NAME]);
            }
        }

        return $dataSource;
    }

    /**
     * Render column text
     *
     * @param int $attributeSetId
     * @return string
     */
    protected function renderColumnText($attributeSetId)
    {
        return $this->attributeSetRepository->get($attributeSetId)->getAttributeSetName();
    }
}
