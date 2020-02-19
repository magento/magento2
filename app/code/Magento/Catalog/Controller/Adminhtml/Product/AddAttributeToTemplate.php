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
use Magento\Eav\Model\Cache\Type as CacheType;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

/**
 * Assign attribute to attribute set.
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
     * @var CacheInterface
     */
    private $cache;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Builder $productBuilder
     * @param JsonFactory $resultJsonFactory
     * @param AttributeGroupInterfaceFactory $attributeGroupFactory
     * @param AttributeRepositoryInterface $attributeRepository
     * @param AttributeSetRepositoryInterface $attributeSetRepository
     * @param AttributeGroupRepositoryInterface $attributeGroupRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param AttributeManagementInterface $attributeManagement
     * @param LoggerInterface $logger
     * @param ExtensionAttributesFactory $extensionAttributesFactory
     * @param CacheInterface|null $cache
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function __construct(
        Context $context,
        Builder $productBuilder,
        JsonFactory $resultJsonFactory,
        AttributeGroupInterfaceFactory $attributeGroupFactory = null,
        AttributeRepositoryInterface $attributeRepository = null,
        AttributeSetRepositoryInterface $attributeSetRepository = null,
        AttributeGroupRepositoryInterface $attributeGroupRepository = null,
        SearchCriteriaBuilder $searchCriteriaBuilder = null,
        AttributeManagementInterface $attributeManagement = null,
        LoggerInterface $logger = null,
        ExtensionAttributesFactory $extensionAttributesFactory = null,
        CacheInterface $cache = null
    ) {
        parent::__construct($context, $productBuilder);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->attributeGroupFactory = $attributeGroupFactory ?: ObjectManager::getInstance()
            ->get(AttributeGroupInterfaceFactory::class);
        $this->attributeRepository = $attributeRepository ?: ObjectManager::getInstance()
            ->get(AttributeRepositoryInterface::class);
        $this->attributeSetRepository = $attributeSetRepository ?: ObjectManager::getInstance()
            ->get(AttributeSetRepositoryInterface::class);
        $this->attributeGroupRepository = $attributeGroupRepository ?: ObjectManager::getInstance()
            ->get(AttributeGroupRepositoryInterface::class);
        $this->searchCriteriaBuilder = $searchCriteriaBuilder ?: ObjectManager::getInstance()
            ->get(SearchCriteriaBuilder::class);
        $this->attributeManagement = $attributeManagement ?: ObjectManager::getInstance()
            ->get(AttributeManagementInterface::class);
        $this->logger = $logger ?: ObjectManager::getInstance()
            ->get(LoggerInterface::class);
        $this->extensionAttributesFactory = $extensionAttributesFactory ?: ObjectManager::getInstance()
            ->get(ExtensionAttributesFactory::class);
        $this->cache = $cache ?? ObjectManager::getInstance()->get(CacheInterface::class);
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
            $attributeSet = $this->attributeSetRepository->get($request->getParam('templateId'));
            $groupCode = $request->getParam('groupCode');
            $groupName = $request->getParam('groupName');
            $groupSortOrder = $request->getParam('groupSortOrder');

            $attributeSearchCriteria = $this->getBasicAttributeSearchCriteriaBuilder()->create();
            $attributeGroupSearchCriteria = $this->searchCriteriaBuilder
                ->addFilter('attribute_set_id', $attributeSet->getAttributeSetId())
                ->addFilter('attribute_group_code', $groupCode)
                ->setPageSize(1)
                ->create();

            try {
                /** @var AttributeGroupInterface[] $attributeGroupItems */
                $attributeGroupItems = $this->attributeGroupRepository
                    ->getList($attributeGroupSearchCriteria)
                    ->getItems();

                if ($attributeGroupItems) {
                    /** @var AttributeGroupInterface $attributeGroup */
                    $attributeGroup = reset($attributeGroupItems);
                } else {
                    /** @var AttributeGroupInterface $attributeGroup */
                    $attributeGroup = $this->attributeGroupFactory->create();
                }
            } catch (NoSuchEntityException $e) {
                /** @var AttributeGroupInterface $attributeGroup */
                $attributeGroup = $this->attributeGroupFactory->create();
            }

            $extensionAttributes = $attributeGroup->getExtensionAttributes()
                ?: $this->extensionAttributesFactory->create(AttributeGroupInterface::class);

            $extensionAttributes->setAttributeGroupCode($groupCode);
            $extensionAttributes->setSortOrder($groupSortOrder);
            $attributeGroup->setAttributeGroupName($groupName);
            $attributeGroup->setAttributeSetId($attributeSet->getAttributeSetId());
            $attributeGroup->setExtensionAttributes($extensionAttributes);

            $this->attributeGroupRepository->save($attributeGroup);

            /** @var AttributeInterface[] $attributesItems */
            $attributesItems = $this->attributeRepository->getList(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                $attributeSearchCriteria
            )->getItems();

            array_walk(
                $attributesItems,
                function (AttributeInterface $attribute) use ($attributeSet, $attributeGroup) {
                    $this->attributeManagement->assign(
                        ProductAttributeInterface::ENTITY_TYPE_CODE,
                        $attributeSet->getAttributeSetId(),
                        $attributeGroup->getAttributeGroupId(),
                        $attribute->getAttributeCode(),
                        '0'
                    );
                }
            );
            $this->cache->clean([CacheType::CACHE_TAG]);
        } catch (LocalizedException $e) {
            $response->setError(true);
            $response->setMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->critical($e);
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
        $attributeIds = (array)$this->getRequest()->getParam('attributeIds', []);

        if (empty($attributeIds['selected'])) {
            throw new LocalizedException(__('Attributes were missing and must be specified.'));
        }

        return $this->searchCriteriaBuilder->addFilter(
            'attribute_id',
            [$attributeIds['selected']],
            'in'
        );
    }
}
