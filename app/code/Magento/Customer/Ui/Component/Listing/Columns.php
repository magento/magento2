<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Ui\Component\Listing;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Customer\Ui\Component\ColumnFactory;
use Magento\Customer\Api\Data\AttributeMetadataInterface;

class Columns extends \Magento\Ui\Component\Listing\Columns
{
    /**
     * @var int
     */
    protected $columnSortOrder;

    /**
     * @var AttributeRepository
     */
    protected $attributeRepository;

    /**
     * @param ContextInterface $context
     * @param ColumnFactory $columnFactory
     * @param AttributeRepository $attributeRepository
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        ColumnFactory $columnFactory,
        AttributeRepository $attributeRepository,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->columnFactory = $columnFactory;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @return int
     */
    protected function getDefaultSortOrder()
    {
        $max = 0;
        foreach ($this->components as $component) {
            $config = $component->getData('config');
            if (isset($config['sortOrder']) && $config['sortOrder'] > $max) {
                $max = $config['sortOrder'];
            }
        }
        return ++$max;
    }

    /**
     * Update actions column sort order
     *
     * @return void
     */
    protected function updateActionColumnSortOrder()
    {
        if (isset($this->components['actions'])) {
            $component = $this->components['actions'];
            $component->setData(
                'config',
                array_merge($component->getData('config'), ['sortOrder' => ++$this->columnSortOrder])
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function prepare()
    {
        $this->columnSortOrder = $this->getDefaultSortOrder();
        foreach ($this->attributeRepository->getList() as $newAttributeCode => $attribute) {
            if (isset($this->components[$attribute->getAttributeCode()])) {
                $this->updateColumn($attribute, $newAttributeCode);
            } elseif ($attribute->getBackendType() != 'static' && $attribute->getIsUsedInGrid()) {
                $this->addColumn($attribute, $newAttributeCode);
            }
        }
        $this->updateActionColumnSortOrder();
        parent::prepare();
    }

    /**
     * @param AttributeMetadataInterface $attribute
     * @param string $columnName
     * @return void
     */
    public function addColumn(AttributeMetadataInterface $attribute, $columnName)
    {
        $config['sortOrder'] = ++$this->columnSortOrder;
        $column = $this->columnFactory->create($attribute, $columnName, $this->getContext(), $config);
        $column->prepare();
        $this->addComponent($attribute->getAttributeCode(), $column);
    }

    /**
     * @param AttributeMetadataInterface $attribute
     * @param string $newAttributeCode
     * @return void
     */
    public function updateColumn(AttributeMetadataInterface $attribute, $newAttributeCode)
    {
        $component = $this->components[$attribute->getAttributeCode()];

        if ($attribute->getBackendType() != 'static') {
            if ($attribute->getIsUsedInGrid()) {
                $config = array_merge(
                    $component->getData('config'),
                    [
                        'name' => $newAttributeCode,
                        'dataType' => $attribute->getBackendType(),
                        'visible' => $attribute->getIsVisibleInGrid()
                    ]
                );
                $component->setData('config', $config);
            }
        } else {
            $component->setData(
                'config',
                array_merge(
                    $component->getData('config'),
                    ['visible' => $attribute->getIsVisibleInGrid()]
                )
            );
        }
    }
}
