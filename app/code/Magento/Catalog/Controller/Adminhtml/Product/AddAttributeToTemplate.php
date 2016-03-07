<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;

class AddAttributeToTemplate extends \Magento\Catalog\Controller\Adminhtml\Product
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var CollectionFactory
     */
    protected $attributeCollectionFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param CollectionFactory $attributeCollectionFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        CollectionFactory $attributeCollectionFactory
    ) {
        parent::__construct($context, $productBuilder);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
    }

    /**
     * Add attribute to attribute set
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $request = $this->getRequest();
        $response = new \Magento\Framework\DataObject();
        $response->setError(false);

        try {
            $attributeSet = $this->_objectManager->create('Magento\Eav\Model\Entity\Attribute\Set')
                ->load($request->getParam('templateId'));

            /** @var \Magento\Eav\Model\ResourceModel\Attribute\Collection $collection */
            $attributesCollection = $this->attributeCollectionFactory->create();

            $attributesIds = $request->getParam('attributesIds');
            if ($attributesIds['excludeMode'] === 'false' && !empty($attributesIds['selected'])) {
                $attributesCollection
                    ->addFieldToFilter('main_table.attribute_id', ['in' => $attributesIds['selected']]);
            } elseif ($attributesIds['excludeMode'] === 'true') {
                $attributesCollection->setExcludeSetFilter($attributeSet->getId());
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(__('Please, specify attributes'));
            }

            $groupCode = $request->getParam('groupCode');

            /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\Collection $attributeGroupCollection */
            $attributeGroupCollection = $this->_objectManager->get(
                'Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\Collection'
            );
            $attributeGroupCollection->setAttributeSetFilter($attributeSet->getId());
            $attributeGroupCollection->addFilter('attribute_group_code', $groupCode);
            $attributeGroupCollection->setPageSize(1);

            $attributeGroup = $attributeGroupCollection->getFirstItem();

            if (!$attributeGroup->getId()) {
                $attributeGroup->addData(
                    [
                        'attribute_group_code' => $groupCode,
                        'attribute_set_id' => $attributeSet->getId(),
                        'attribute_group_name' => $request->getParam('groupName'),
                        'sort_order' => $request->getParam('groupSortOrder')
                    ]
                );
                $attributeGroup->save();
            }

            foreach ($attributesCollection as $attribute) {
                $attribute->setAttributeSetId($attributeSet->getId())->loadEntityAttributeIdBySet();
                $attribute->setAttributeGroupId($attributeGroup->getId())
                    ->setSortOrder('0')
                    ->save();
            }
        } catch (\Exception $e) {
            $response->setError(true);
            $response->setMessage($e->getMessage());
        }
        return $this->resultJsonFactory->create()->setJsonData($response->toJson());
    }
}
