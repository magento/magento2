<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Attribute;

use Magento\Catalog\Model\Category;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Eav\Model\Entity\Attribute as EavAttribute;
use Magento\Ui\Component\Form;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Element\DataType\Text;

/**
 * Data provider for the form of adding new product attribute.
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * DataProvider constructor.
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param StoreRepositoryInterface $storeRepository
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        StoreRepositoryInterface $storeRepository,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->storeRepository = $storeRepository;
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

        $meta['advanced_fieldset']['children'] = [
            'attribute_code' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'notice' => __(
                                'This is used internally. Make sure you don\'t use spaces or more than %1 symbols.',
                                EavAttribute::ATTRIBUTE_CODE_MAX_LENGTH
                            ),
                            'validation' => [
                                'max_text_length' => EavAttribute::ATTRIBUTE_CODE_MAX_LENGTH
                            ]
                        ]
                    ]
                ]
            ]
        ];

        foreach ($this->storeRepository->getList() as $store) {
            if ($store->getId()) {
                $meta['manage-titles']['children'] = [
                    'frontend_label[' . $store->getId() . ']' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'formElement' => Input::NAME,
                                    'componentType' => Field::NAME,
                                    'label' => $store->getName(),
                                    'dataType' => Text::NAME,
                                    'dataScope' => 'frontend_label[' . $store->getId() . ']'
                                ]
                            ]
                        ]
                    ]
                ];
            }
        }

        return $meta;
    }
}
