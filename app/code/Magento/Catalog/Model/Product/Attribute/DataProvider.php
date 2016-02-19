<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Attribute;

use Magento\Catalog\Model\Category;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Eav\Model\Entity\Attribute as EavAttribute;
use Magento\Ui\Component\Form;

/**
 * Data provider for the form of adding new product attribute.
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * DataProvider constructor.
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getMeta()
    {
        $meta = parent::getMeta();

        $scopes = [
            [
                'value' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'label' => __('Store View')
            ],
            [
                'value' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_WEBSITE,
                'label' => __('Website')
            ],
            [
                'value' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'label' => __('Global')
            ]
        ];

        $meta['advanced_fieldset']['children'] = [
            'is_global' => ['options' => $scopes],
            'attribute_code' => [
                'notice' => __(
                    "This is used internally. Make sure you don't use spaces or more than %1 symbols.",
                    EavAttribute::ATTRIBUTE_CODE_MAX_LENGTH
                ),
                'validation' => [
                    'validate-length' => true,
                    'maximum-length-' . EavAttribute::ATTRIBUTE_CODE_MAX_LENGTH => true
                ]
            ]
        ];

        return $meta;
    }
}
