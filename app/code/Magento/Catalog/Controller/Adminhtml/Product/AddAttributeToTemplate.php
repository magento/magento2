<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Controller\Adminhtml\Product;

use Magento\Backend\App\Action\Context;
use Magento\Catalog\Api\AttributeSetRepositoryInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Controller\Adminhtml\Product;
use Magento\Eav\Api\AttributeGroupRepositoryInterface;
use Magento\Eav\Api\AttributeManagementInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Api\Data\AttributeGroupInterface;
use Magento\Eav\Api\Data\AttributeGroupInterfaceFactory;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Api\Data\AttributeSetInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\ObjectManager;
use Psr\Log\LoggerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\ExtensionAttributesFactory;

/**
 * Class AddAttributeToTemplate
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddAttributeToTemplate extends Product implements HttpPostActionInterface
{
    /**
     * @var JsonFactory
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
     * @var AttributeGroupInterfaceFactory
     */
    protected $attributeGroupFactory;

    /**
     * @var AttributeManagementInterface
     */
    protected $attributeManagement;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ExtensionAttributesFactory
     */
    protected $extensionAttributesFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Builder $productBuilder
     * @param JsonFactory $resultJsonFactory
     * @param AttributeGroupInterfaceFactory|null $attributeGroupFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        Context $context,
        Builder $productBuilder,
        JsonFactory $resultJsonFactory,
        AttributeGroupInterfaceFactory $attributeGroupFactory = null
    ) {
        parent::__construct($context, $productBuilder);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->attributeGroupFactory = $attributeGroupFactory ?: ObjectManager::getInstance()
            ->get(AttributeGroupInterfaceFactory::class);
    }

    /**
     * Add attribute to attribute set
     *
     * @return Json
     */
    public function execute()
    {
        $request = $this->getRequest();
        $response = new DataObject();
        $response->setError(false);

        try {
            /** @var AttributeSetInterface $attributeSet */
            $attributeSet = $this->getAttributeSetRepository()->get($request->getParam('templateId'));
            $groupCode = $request->getParam('groupCode');
            $groupName = $request->getParam('groupName');
            $groupSortOrder = $request->getParam('groupSortOrder');

            $attributeSearchCriteria = $this->getBasicAttributeSearchCriteriaBuilder()->create();
            $attributeGroupSearchCriteria = $this->getSearchCriteriaBuilder()
                ->addFilter('attribute_set_id', $attributeSet->getAttributeSetId())
                ->addFilter('attribute_group_code', $groupCode)
                ->setPageSize(1)
                ->create();

            try {
                /** @var AttributeGroupInterface[] $attributeGroupItems */
                $attributeGroupItems = $this->getAttributeGroupRepository()->getList($attributeGroupSearchCriteria)
                    ->getItems();

                if (!$attributeGroupItems) {
                    throw new NoSuchEntityException;
                }

                /** @var AttributeGroupInterface $attributeGroup */
                $attributeGroup = reset($attributeGroupItems);
            } catch (NoSuchEntityException $e) {
                /** @var AttributeGroupInterface $attributeGroup */
                $attributeGroup = $this->attributeGroupFactory->create();
            }

            $extensionAttributes = $attributeGroup->getExtensionAttributes()
                ?: $this->getExtensionAttributesFactory()->create(AttributeGroupInterface::class);

            $extensionAttributes->setAttributeGroupCode($groupCode);
            $extensionAttributes->setSortOrder($groupSortOrder);
            $attributeGroup->setAttributeGroupName($groupName);
            $attributeGroup->setAttributeSetId($attributeSet->getAttributeSetId());
            $attributeGroup->setExtensionAttributes($extensionAttributes);

            $this->getAttributeGroupRepository()->save($attributeGroup);

            /** @var AttributeInterface[] $attributesItems */
            $attributesItems = $this->getAttributeRepository()->getList(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                $attributeSearchCriteria
            )->getItems();

            array_walk($attributesItems, function (AttributeInterface $attribute) use ($attributeSet, $attributeGroup) {
                $this->getAttributeManagement()->assign(
                    ProductAttributeInterface::ENTITY_TYPE_CODE,
                    $attributeSet->getAttributeSetId(),
                    $attributeGroup->getAttributeGroupId(),
                    $attribute->getAttributeCode(),
                    '0'
                );
            });
        } catch (LocalizedException $e) {
            $response->setError(true);
            $response->setMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->getLogger()->critical($e);
            $response->setError(true);
            $response->setMessage(__('Unable to add attribute'));
        }

        return $this->resultJsonFactory->create()->setJsonData($response->toJson());
    }

    /**
     * Adding basic filters
     *
     * @return SearchCriteriaBuilder
     * @throws LocalizedException
     */
    private function getBasicAttributeSearchCriteriaBuilder()
    {
        $attributeIds = (array) $this->getRequest()->getParam('attributeIds', []);

        if (empty($attributeIds['selected'])) {
            throw new LocalizedException(__('Attributes were missing and must be specified.'));
        }

        return $this->getSearchCriteriaBuilder()
            ->addFilter('attribute_id', [$attributeIds['selected']], 'in');
    }

    /**
     * Get AttributeRepositoryInterface
     *
     * @return AttributeRepositoryInterface
     */
    private function getAttributeRepository()
    {
        if (null === $this->attributeRepository) {
            $this->attributeRepository = ObjectManager::getInstance()
                ->get(AttributeRepositoryInterface::class);
        }
        return $this->attributeRepository;
    }

    /**
     * Get AttributeSetRepositoryInterface
     *
     * @return AttributeSetRepositoryInterface
     */
    private function getAttributeSetRepository()
    {
        if (null === $this->attributeSetRepository) {
            $this->attributeSetRepository = ObjectManager::getInstance()
                ->get(AttributeSetRepositoryInterface::class);
        }
        return $this->attributeSetRepository;
    }

    /**
     * Get AttributeGroupInterface
     *
     * @return AttributeGroupRepositoryInterface
     */
    private function getAttributeGroupRepository()
    {
        if (null === $this->attributeGroupRepository) {
            $this->attributeGroupRepository = ObjectManager::getInstance()
                ->get(AttributeGroupRepositoryInterface::class);
        }
        return $this->attributeGroupRepository;
    }

    /**
     * Get SearchCriteriaBuilder
     *
     * @return SearchCriteriaBuilder
     */
    private function getSearchCriteriaBuilder()
    {
        if (null === $this->searchCriteriaBuilder) {
            $this->searchCriteriaBuilder = ObjectManager::getInstance()
                ->get(SearchCriteriaBuilder::class);
        }
        return $this->searchCriteriaBuilder;
    }

    /**
     * Get AttributeManagementInterface
     *
     * @return AttributeManagementInterface
     */
    private function getAttributeManagement()
    {
        if (null === $this->attributeManagement) {
            $this->attributeManagement = ObjectManager::getInstance()
                ->get(AttributeManagementInterface::class);
        }
        return $this->attributeManagement;
    }

    /**
     * Get LoggerInterface
     *
     * @return LoggerInterface
     */
    private function getLogger()
    {
        if (null === $this->logger) {
            $this->logger = ObjectManager::getInstance()
                ->get(LoggerInterface::class);
        }
        return $this->logger;
    }

    /**
     * Get ExtensionAttributesFactory.
     *
     * @return ExtensionAttributesFactory
     */
    private function getExtensionAttributesFactory()
    {
        if (null === $this->extensionAttributesFactory) {
            $this->extensionAttributesFactory = ObjectManager::getInstance()
                ->get(ExtensionAttributesFactory::class);
        }
        return $this->extensionAttributesFactory;
    }
}
