<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Attribute;

use Magento\Framework\Stdlib\ArrayManager;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Eav\Model\Entity\Attribute as EavAttribute;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Store\Model\Store;

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
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param StoreRepositoryInterface $storeRepository
     * @param ArrayManager $arrayManager
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        StoreRepositoryInterface $storeRepository,
        ArrayManager $arrayManager,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->storeRepository = $storeRepository;
        $this->arrayManager = $arrayManager;
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
     * Get meta information
     *
     * @return array
     */
    public function getMeta()
    {
        $meta = parent::getMeta();

        $meta = $this->customizeAttributeCode($meta);
        $meta = $this->customizeFrontendLabels($meta);
        $meta = $this->customizeOptions($meta);

        return $meta;
    }

    /**
     * Customize attribute_code field
     *
     * @param array $meta
     * @return array
     */
    private function customizeAttributeCode($meta)
    {
        $meta['advanced_fieldset']['children'] = $this->arrayManager->set(
            'attribute_code/arguments/data/config',
            [],
            [
                'notice' => __(
                    'This is used internally. Make sure you don\'t use spaces or more than %1 symbols.',
                    EavAttribute::ATTRIBUTE_CODE_MAX_LENGTH
                ),
                'validation' => [
                    'max_text_length' => EavAttribute::ATTRIBUTE_CODE_MAX_LENGTH
                ]
            ]
        );
        return $meta;
    }

    /**
     * Customize frontend labels
     *
     * @param array $meta
     * @return array
     */
    private function customizeFrontendLabels($meta)
    {
        foreach ($this->storeRepository->getList() as $store) {
            $storeId = $store->getId();

            if (!$storeId) {
                continue;
            }

            $meta['manage-titles']['children'] = [
                'frontend_label[' . $storeId . ']' => $this->arrayManager->set(
                    'arguments/data/config',
                    [],
                    [
                        'formElement' => Input::NAME,
                        'componentType' => Field::NAME,
                        'label' => $store->getName(),
                        'dataType' => Text::NAME,
                        'dataScope' => 'frontend_label[' . $storeId . ']'
                    ]
                ),
            ];
        }
        return $meta;
    }

    /**
     * Customize options
     *
     * @param array $meta
     * @return array
     */
    private function customizeOptions($meta)
    {
        $sortOrder = 1;
        foreach ($this->storeRepository->getList() as $store) {
            $storeId = $store->getId();
            $storeLabelConfiguration = [
                'dataType' => 'text',
                'formElement' => 'input',
                'component' => 'Magento_Catalog/js/form/element/input',
                'template' => 'Magento_Catalog/form/element/input',
                'prefixName' => 'option.value',
                'prefixElementName' => 'option_',
                'suffixName' => (string)$storeId,
                'label' => $store->getName(),
                'sortOrder' => $sortOrder,
                'componentType' => Field::NAME,
            ];
            // JS code can't understand 'required-entry' => false|null, we have to avoid even empty property.
            if ($store->getCode() == Store::ADMIN_CODE) {
                $storeLabelConfiguration['validation'] = [
                    'required-entry' => true,
                ];
            }
            $meta['attribute_options_select_container']['children']['attribute_options_select']['children']
            ['record']['children']['value_option_' . $storeId] = $this->arrayManager->set(
                'arguments/data/config',
                [],
                $storeLabelConfiguration
            );

            $meta['attribute_options_multiselect_container']['children']['attribute_options_multiselect']['children']
            ['record']['children']['value_option_' . $storeId] = $this->arrayManager->set(
                'arguments/data/config',
                [],
                $storeLabelConfiguration
            );
            ++$sortOrder;
        }

        $meta['attribute_options_select_container']['children']['attribute_options_select']['children']
        ['record']['children']['action_delete'] = $this->arrayManager->set(
            'arguments/data/config',
            [],
            [
                'componentType' => 'actionDelete',
                'dataType' => 'text',
                'fit' => true,
                'sortOrder' => $sortOrder,
                'component' => 'Magento_Catalog/js/form/element/action-delete',
                'elementTmpl' => 'Magento_Catalog/form/element/action-delete',
                'template' => 'Magento_Catalog/form/element/action-delete',
                'prefixName' => 'option.delete',
                'prefixElementName' => 'option_',
            ]
        );
        $meta['attribute_options_multiselect_container']['children']['attribute_options_multiselect']['children']
        ['record']['children']['action_delete'] = $this->arrayManager->set(
            'arguments/data/config',
            [],
            [
                'componentType' => 'actionDelete',
                'dataType' => 'text',
                'fit' => true,
                'sortOrder' => $sortOrder,
                'component' => 'Magento_Catalog/js/form/element/action-delete',
                'elementTmpl' => 'Magento_Catalog/form/element/action-delete',
                'template' => 'Magento_Catalog/form/element/action-delete',
                'prefixName' => 'option.delete',
                'prefixElementName' => 'option_',
            ]
        );
        return $meta;
    }
}
