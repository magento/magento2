<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Model\TaxClass\Source;

use Magento\Framework\DB\Ddl\Table;
use Magento\Tax\Api\Data\TaxClassInterface as TaxClass;
use Magento\Tax\Api\TaxClassManagementInterface;

/**
 * Product tax class source model.
 */
class Product extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @var \Magento\Tax\Api\TaxClassRepositoryInterface
     */
    protected $_taxClassRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $_searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $_filterBuilder;

    /**
     * Core data
     *
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreData;

    /**
     * @var \Magento\Eav\Model\Resource\Entity\Attribute\OptionFactory
     */
    protected $_optionFactory;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Tax\Model\Resource\TaxClass\CollectionFactory $classesFactory
     * @param \Magento\Eav\Model\Resource\Entity\Attribute\OptionFactory $optionFactory
     * @param \Magento\Tax\Api\TaxClassRepositoryInterface $taxClassRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     */
    public function __construct(
        \Magento\Core\Helper\Data $coreData,
        \Magento\Tax\Model\Resource\TaxClass\CollectionFactory $classesFactory,
        \Magento\Eav\Model\Resource\Entity\Attribute\OptionFactory $optionFactory,
        \Magento\Tax\Api\TaxClassRepositoryInterface $taxClassRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder
    ) {
        $this->_coreData = $coreData;
        $this->_classesFactory = $classesFactory;
        $this->_optionFactory = $optionFactory;
        $this->_taxClassRepository = $taxClassRepository;
        $this->_filterBuilder = $filterBuilder;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Retrieve all product tax class options.
     *
     * @param bool $withEmpty
     * @return array
     */
    public function getAllOptions($withEmpty = true)
    {
        if (!$this->_options) {
            $filter = $this->_filterBuilder
                ->setField(TaxClass::KEY_TYPE)
                ->setValue(TaxClassManagementInterface::TYPE_PRODUCT)
                ->create();
            $searchCriteria = $this->_searchCriteriaBuilder->addFilter([$filter])->create();
            $searchResults = $this->_taxClassRepository->getList($searchCriteria);
            foreach ($searchResults->getItems() as $taxClass) {
                $this->_options[] = [
                    'value' => $taxClass->getClassId(),
                    'label' => $taxClass->getClassName(),
                ];
            }
        }

        if ($withEmpty) {
            if (!$this->_options) {
                return [['value' => '0', 'label' => __('None')]];
            } else {
                return array_merge([['value' => '0', 'label' => __('None')]], $this->_options);
            }
        }
        return $this->_options;
    }

    /**
     * Get a text for option value
     *
     * @param string|integer $value
     * @return string
     */
    public function getOptionText($value)
    {
        $options = $this->getAllOptions();

        foreach ($options as $item) {
            if ($item['value'] == $value) {
                return $item['label'];
            }
        }
        return false;
    }

    /**
     * Retrieve flat column definition
     *
     * @return array
     */
    public function getFlatColumns()
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();

        return [
            $attributeCode => [
                'unsigned' => true,
                'default' => null,
                'extra' => null,
                'type' => Table::TYPE_INTEGER,
                'nullable' => true,
                'comment' => $attributeCode . ' tax column',
            ],
        ];
    }

    /**
     * Retrieve Select for update attribute value in flat table
     *
     * @param   int $store
     * @return  \Magento\Framework\DB\Select|null
     */
    public function getFlatUpdateSelect($store)
    {
        /** @var $option \Magento\Eav\Model\Resource\Entity\Attribute\Option */
        $option = $this->_optionFactory->create();
        return $option->getFlatUpdateSelect($this->getAttribute(), $store, false);
    }
}
