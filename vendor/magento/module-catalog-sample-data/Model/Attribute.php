<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSampleData\Model;

use Magento\Framework\Setup\SampleData\Context as SampleDataContext;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Setup sample attributes
 *
 * Class Attribute
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Attribute
{
    /**
     * @var \Magento\Framework\Setup\SampleData\FixtureManager
     */
    protected $fixtureManager;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory
     */
    protected $attributeFactory;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\SetFactory
     */
    protected $attributeSetFactory;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory
     */
    protected $attrOptionCollectionFactory;

    /**
     * @var \Magento\Catalog\Helper\Product
     */
    protected $productHelper;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csvReader;

    /**
     * @var int
     */
    protected $entityTypeId;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param SampleDataContext $sampleDataContext
     * @param \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory $attributeFactory
     * @param \Magento\Eav\Model\Entity\Attribute\SetFactory $attributeSetFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory
     * @param \Magento\Catalog\Helper\Product $productHelper
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        SampleDataContext $sampleDataContext,
        \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory $attributeFactory,
        \Magento\Eav\Model\Entity\Attribute\SetFactory $attributeSetFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->fixtureManager = $sampleDataContext->getFixtureManager();
        $this->csvReader = $sampleDataContext->getCsvReader();
        $this->attributeFactory = $attributeFactory;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->attrOptionCollectionFactory = $attrOptionCollectionFactory;
        $this->productHelper = $productHelper;
        $this->eavConfig = $eavConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * @param array $fixtures
     * @throws \Exception
     */
    public function install(array $fixtures)
    {
        $attributeCount = 0;
        foreach ($fixtures as $fileName) {
            $fileName = $this->fixtureManager->getFixture($fileName);
            if (!file_exists($fileName)) {
                continue;
            }

            $rows = $this->csvReader->getData($fileName);
            $header = array_shift($rows);

            foreach ($rows as $row) {
                $data = [];
                foreach ($row as $key => $value) {
                    $data[$header[$key]] = $value;
                }
                $data['attribute_set'] = explode("\n", $data['attribute_set']);

                /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
                $attribute = $this->eavConfig->getAttribute('catalog_product', $data['attribute_code']);
                if (!$attribute) {
                    $attribute = $this->attributeFactory->create();
                }

                $frontendLabel = explode("\n", $data['frontend_label']);
                if (count($frontendLabel) > 1) {
                    $data['frontend_label'] = [];
                    $data['frontend_label'][\Magento\Store\Model\Store::DEFAULT_STORE_ID] = $frontendLabel[0];
                    $data['frontend_label'][$this->storeManager->getDefaultStoreView()->getStoreId()] =
                        $frontendLabel[1];
                }
                $data['option'] = $this->getOption($attribute, $data);
                $data['source_model'] = $this->productHelper->getAttributeSourceModelByInputType(
                    $data['frontend_input']
                );
                $data['backend_model'] = $this->productHelper->getAttributeBackendModelByInputType(
                    $data['frontend_input']
                );
                $data += ['is_filterable' => 0, 'is_filterable_in_search' => 0, 'apply_to' => []];
                $data['backend_type'] = $attribute->getBackendTypeByInput($data['frontend_input']);

                $attribute->addData($data);
                $attribute->setIsUserDefined(1);

                $attribute->setEntityTypeId($this->getEntityTypeId());
                $attribute->save();
                $attributeId = $attribute->getId();

                if (is_array($data['attribute_set'])) {
                    foreach ($data['attribute_set'] as $setName) {
                        $setName = trim($setName);
                        $attributeCount++;
                        $attributeSet = $this->processAttributeSet($setName);
                        $attributeGroupId = $attributeSet->getDefaultGroupId();

                        $attribute = $this->attributeFactory->create();
                        $attribute
                            ->setId($attributeId)
                            ->setAttributeGroupId($attributeGroupId)
                            ->setAttributeSetId($attributeSet->getId())
                            ->setEntityTypeId($this->getEntityTypeId())
                            ->setSortOrder($attributeCount + 999)
                            ->save();
                    }
                }
            }
        }
        $this->eavConfig->clear();
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute
     * @param array $data
     * @return array
     */
    protected function getOption($attribute, $data)
    {
        $result = [];
        $data['option'] = explode("\n", $data['option']);
        /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection $options */
        $options = $this->attrOptionCollectionFactory->create()
            ->setAttributeFilter($attribute->getId())
            ->setPositionOrder('asc', true)
            ->load();
        foreach ($data['option'] as $value) {
            if (!$options->getItemByColumnValue('value', $value)) {
                $result[] = $value;
            }
        }
        return $result ? $this->convertOption($result) : $result;
    }

    /**
     * Converting attribute options from csv to correct sql values
     *
     * @param array $values
     * @return array
     */
    protected function convertOption($values)
    {
        $result = ['order' => [], 'value' => []];
        $i = 0;
        foreach ($values as $value) {
            $result['order']['option_' . $i] = (string)$i;
            $result['value']['option_' . $i] = [0 => $value, 1 => ''];
            $i++;
        }
        return $result;
    }

    /**
     * @return int
     * @throws \Magento\Framework\Model\Exception
     */
    protected function getEntityTypeId()
    {
        if (!$this->entityTypeId) {
            $this->entityTypeId = $this->eavConfig->getEntityType(\Magento\Catalog\Model\Product::ENTITY)->getId();
        }
        return $this->entityTypeId;
    }

    /**
     * Loads attribute set by name if attribute with such name exists
     * Otherwise creates the attribute set with $setName name and return it
     *
     * @param string $setName
     * @return \Magento\Eav\Model\Entity\Attribute\Set
     * @throws \Exception
     * @throws \Magento\Framework\Model\Exception
     */
    protected function processAttributeSet($setName)
    {
        /** @var \Magento\Eav\Model\Entity\Attribute\Set $attributeSet */
        $attributeSet = $this->attributeSetFactory->create();
        $setCollection = $attributeSet->getResourceCollection()
            ->addFieldToFilter('entity_type_id', $this->getEntityTypeId())
            ->addFieldToFilter('attribute_set_name', $setName)
            ->load();
        $attributeSet = $setCollection->fetchItem();

        if (!$attributeSet) {
            $attributeSet = $this->attributeSetFactory->create();
            $attributeSet->setEntityTypeId($this->getEntityTypeId());
            $attributeSet->setAttributeSetName($setName);
            $attributeSet->save();
            $defaultSetId = $this->eavConfig->getEntityType(\Magento\Catalog\Model\Product::ENTITY)
                ->getDefaultAttributeSetId();
            $attributeSet->initFromSkeleton($defaultSetId);
            $attributeSet->save();
        }
        return $attributeSet;
    }
}
