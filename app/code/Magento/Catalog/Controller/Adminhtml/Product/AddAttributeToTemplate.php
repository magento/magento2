<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product;

use Magento\Catalog\Api\AttributeSetRepositoryInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Api\AttributeGroupRepositoryInterface;
use Magento\Eav\Api\AttributeManagementInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Api\Data\AttributeGroupInterface;
use Magento\Eav\Api\Data\AttributeGroupInterfaceFactory;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Api\Data\AttributeSetInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;

/**
 * Class AddAttributeToTemplate
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddAttributeToTemplate extends \Magento\Catalog\Controller\Adminhtml\Product
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var AttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var AttributeSetRepositoryInterface
     */
    protected $attributeSetRepository;

    /**
     * @var AttributeGroupRepositoryInterface
     */
    protected $attributeGroupRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var SortOrderBuilder
     */
    protected $sortOrderBuilder;

    /**
     * @var AttributeGroupInterfaceFactory
     */
    protected $attributeGroupFactory;

    /**
     * @var AttributeManagementInterfaces
     */
    protected $attributeManagement;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param Builder $productBuilder
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param AttributeRepositoryInterface $attributeRepository
     * @param AttributeSetRepositoryInterface $attributeSetRepository
     * @param AttributeGroupRepositoryInterface $attributeGroupRepository
     * @param AttributeGroupInterfaceFactory $attributeGroupFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     * @param AttributeManagementInterface $attributeManagement
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        AttributeRepositoryInterface $attributeRepository,
        AttributeSetRepositoryInterface $attributeSetRepository,
        AttributeGroupRepositoryInterface $attributeGroupRepository,
        AttributeGroupInterfaceFactory $attributeGroupFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder,
        AttributeManagementInterface $attributeManagement
    ) {
        parent::__construct($context, $productBuilder);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->attributeRepository = $attributeRepository;
        $this->attributeSetRepository = $attributeSetRepository;
        $this->attributeGroupRepository = $attributeGroupRepository;
        $this->attributeGroupFactory = $attributeGroupFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->attributeManagement = $attributeManagement;
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

        $attributeSearchCriteriaBuilder = clone $this->searchCriteriaBuilder;
        $groupSearchCriteriaBuilder = clone $this->searchCriteriaBuilder;

        try {
            /** @var AttributeSetInterface $attributeSet */
            $attributeSet = $this->attributeSetRepository->get($request->getParam('templateId'));
            $groupCode = $request->getParam('groupCode');
            $groupName = $request->getParam('groupName');
            $groupSortOrder = $request->getParam('groupSortOrder');

            $attributeSearchCriteriaBuilder = $this->addBasicAttributeSearchFilters($attributeSearchCriteriaBuilder);

            $attributeSearchCriteria = $attributeSearchCriteriaBuilder->create();
            $attributeGroupSearchCriteria = $groupSearchCriteriaBuilder
                ->addFilter('attribute_set_id', $attributeSet->getAttributeSetId())
                ->addFilter('attribute_group_code', $groupCode)
                ->addSortOrder($this->sortOrderBuilder->setAscendingDirection()->create())
                ->setPageSize(1)
                ->create();

            try {
                /** @var AttributeGroupInterface[] $attributeGroupItems */
                $attributeGroupItems = $this->attributeGroupRepository->getList($attributeGroupSearchCriteria)
                    ->getItems();

                if (!$attributeGroupItems) {
                    throw new \Magento\Framework\Exception\NoSuchEntityException;
                }

                /** @var AttributeGroupInterface $attributeGroup */
                $attributeGroup = reset($attributeGroupItems);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                /** @var AttributeGroupInterface $attributeGroup */
                $attributeGroup = $this->attributeGroupFactory->create();
            }

            $attributeGroup->setAttributeGroupCode($groupCode);
            $attributeGroup->setSortOrder($groupSortOrder);
            $attributeGroup->setAttributeGroupName($groupName);
            $attributeGroup->setAttributeSetId($attributeSet->getAttributeSetId());

            $this->attributeGroupRepository->save($attributeGroup);

            /** @var AttributeInterface[] $attributesItems */
            $attributesItems = $this->attributeRepository->getList(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                $attributeSearchCriteria
            )->getItems();

            array_walk($attributesItems, function (AttributeInterface $attribute) use ($attributeSet, $attributeGroup) {
                $this->attributeManagement->assign(
                    ProductAttributeInterface::ENTITY_TYPE_CODE,
                    $attributeSet->getAttributeSetId(),
                    $attributeGroup->getAttributeGroupId(),
                    $attribute->getAttributeCode(),
                    '0'
                );
            });
        } catch (\LocalizedException $e) {
            $response->setError(true);
            $response->setMessage($e->getMessage());
        } catch (\Exception $e) {
            $response->setError(true);
            $response->setMessage(__('Unable to add attribute'));
        }

        return $this->resultJsonFactory->create()->setJsonData($response->toJson());
    }

    /**
     * Adding basic filters
     *
     * @param SearchCriteriaBuilder $attributeSearchCriteriaBuilder
     * @return SearchCriteriaBuilder
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function addBasicAttributeSearchFilters(
        SearchCriteriaBuilder $attributeSearchCriteriaBuilder
    ) {
        $attributeIds = (array)$this->getRequest()->getParam('attributeIds', []);

        if (!empty($attributeIds['selected'])) {
            return $attributeSearchCriteriaBuilder->addFilter(
                'attribute_id',
                [$attributeIds['selected']],
                'in'
            );
        }

        $attributeSearchCriteriaBuilder->addFilter('attribute_set_id', null);

        throw new \Magento\Framework\Exception\LocalizedException(__('Please, specify attributes'));
    }
}
