<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Form\Field;

/**
 * Add "Attribute Set" to first fieldset
 */
class AttributeSet extends AbstractModifier
{
    /**
     * Sort order of "Attribute Set" field inside of fieldset
     */
    const ATTRIBUTE_SET_FIELD_ORDER = 30;

    /**
     * Set collection factory
     *
     * @var CollectionFactory
     */
    protected $attributeSetCollectionFactory;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @param LocatorInterface $locator
     * @param CollectionFactory $attributeSetCollectionFactory
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        LocatorInterface $locator,
        CollectionFactory $attributeSetCollectionFactory,
        UrlInterface $urlBuilder
    ) {
        $this->locator = $locator;
        $this->attributeSetCollectionFactory = $attributeSetCollectionFactory;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Return options for select
     *
     * @return array
     */
    public function getOptions()
    {
        /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $collection */
        $collection = $this->attributeSetCollectionFactory->create();
        $collection->setEntityTypeFilter($this->locator->getProduct()->getResource()->getTypeId())
            ->addFieldToSelect('attribute_set_id', 'value')
            ->addFieldToSelect('attribute_set_name', 'label')
            ->setOrder(
                'attribute_set_name',
                \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection::SORT_ORDER_ASC
            );

        return $collection->getData();
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        if ($name = $this->getGeneralPanelName($meta)) {
            $meta[$name]['children']['attribute_set_id']['arguments']['data']['config']  = [
                'component' => 'Magento_Catalog/js/components/attribute-set-select',
                'disableLabel' => true,
                'filterOptions' => true,
                'elementTmpl' => 'ui/grid/filters/elements/ui-select',
                'formElement' => 'select',
                'componentType' => Field::NAME,
                'options' => $this->getOptions(),
                'visible' => 1,
                'required' => 1,
                'label' => __('Attribute Set'),
                'source' => $name,
                'dataScope' => 'attribute_set_id',
                'filterUrl' => $this->urlBuilder->getUrl('catalog/product/suggestAttributeSets', ['isAjax' => 'true']),
                'sortOrder' => $this->getNextAttributeSortOrder(
                    $meta,
                    [ProductAttributeInterface::CODE_STATUS],
                    self::ATTRIBUTE_SET_FIELD_ORDER
                ),
                'multiple' => false,
            ];
        }

        return $meta;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        return array_replace_recursive($data, [
            $this->locator->getProduct()->getId() => [
                self::DATA_SOURCE_DEFAULT => [
                    'attribute_set_id' => $this->locator->getProduct()->getAttributeSetId()
                ],
            ]
        ]);
    }
}
