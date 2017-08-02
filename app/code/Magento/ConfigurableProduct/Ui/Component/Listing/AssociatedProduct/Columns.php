<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Ui\Component\Listing\AssociatedProduct;

/**
 * Class \Magento\ConfigurableProduct\Ui\Component\Listing\AssociatedProduct\Columns
 *
 * @since 2.0.0
 */
class Columns extends \Magento\Ui\Component\Listing\Columns
{
    /**
     * @var \Magento\Catalog\Ui\Component\Listing\Attribute\RepositoryInterface
     * @since 2.0.0
     */
    protected $attributeRepository;

    /**
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Catalog\Ui\Component\ColumnFactory $columnFactory
     * @param \Magento\Catalog\Ui\Component\Listing\Attribute\RepositoryInterface $attributeRepository
     * @param array $components
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Catalog\Ui\Component\ColumnFactory $columnFactory,
        \Magento\Catalog\Ui\Component\Listing\Attribute\RepositoryInterface $attributeRepository,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->columnFactory = $columnFactory;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function prepare()
    {
        foreach ($this->attributeRepository->getList() as $attribute) {
            $column = $this->columnFactory->create($attribute, $this->getContext());
            $column->prepare();
            $this->addComponent($attribute->getAttributeCode(), $column);
        }
        parent::prepare();
    }
}
