<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Ui\Component\Listing\AssociatedProduct;

class Filters extends \Magento\Ui\Component\Filters
{
    /** @var \Magento\Catalog\Ui\Component\Listing\Attribute\RepositoryInterface */
    protected $attributeRepository;

    /**
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Catalog\Ui\Component\FilterFactory $filterFactory
     * @param \Magento\Catalog\Ui\Component\Listing\Attribute\RepositoryInterface $attributeRepository
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Catalog\Ui\Component\FilterFactory $filterFactory,
        \Magento\Catalog\Ui\Component\Listing\Attribute\RepositoryInterface $attributeRepository,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->filterFactory = $filterFactory;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare()
    {
        $attributeIds = $this->getContext()->getRequestParam('attributes_codes');
        if ($attributeIds) {
            foreach ($this->attributeRepository->getList() as $attribute) {
                $filter = $this->filterFactory->create($attribute, $this->getContext(), ['component' => '']);
                $filter->prepare();
                $this->addComponent($attribute->getAttributeCode(), $filter);
            }
        }
        parent::prepare();
    }
}
