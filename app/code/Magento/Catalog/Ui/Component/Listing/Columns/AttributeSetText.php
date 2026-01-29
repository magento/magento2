<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Ui\Component\Listing\Columns;

use Magento\Ui\Component\Listing\Columns\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Eav\Api\AttributeSetRepositoryInterface;

/**
 * @api
 * @since 101.0.0
 */
class AttributeSetText extends Column
{
    /**
     * Column name
     */
    public const NAME = 'attribute_set_id';

    /**
     * @var AttributeSetRepositoryInterface
     * @since 101.0.0
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
     * @since 101.0.0
     */
    public function prepareDataSource(array $dataSource)
    {
        $dataSource = parent::prepareDataSource($dataSource);

        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }

        $fieldName = $this->getData('name') ?? '';

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
     * @since 101.0.0
     */
    protected function renderColumnText($attributeSetId)
    {
        return $this->attributeSetRepository->get($attributeSetId)->getAttributeSetName();
    }
}
