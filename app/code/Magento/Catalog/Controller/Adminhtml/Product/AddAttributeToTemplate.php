<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use Magento\Framework\Api\ExtensionAttributesFactory;

/**
 * Class AddAttributeToTemplate
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class AddAttributeToTemplate extends \Magento\Catalog\Controller\Adminhtml\Product
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     * @since 2.0.0
     */
    protected $resultJsonFactory;

    /**
     * @var AttributeRepositoryInterface
     * @since 2.1.0
     */
    protected $attributeRepository;

    /**
     * @var AttributeSetRepositoryInterface
     * @since 2.1.0
     */
    protected $attributeSetRepository;

    /**
     * @var AttributeGroupRepositoryInterface
     * @since 2.1.0
     */
    protected $attributeGroupRepository;

    /**
     * @var SearchCriteriaBuilder
     * @since 2.1.0
     */
    protected $searchCriteriaBuilder;

    /**
     * @var AttributeGroupInterfaceFactory
     * @since 2.1.0
     */
    protected $attributeGroupFactory;

    /**
     * @var AttributeManagementInterface
     * @since 2.1.0
     */
    protected $attributeManagement;

    /**
     * @var LoggerInterface
     * @since 2.1.0
     */
    protected $logger;

    /**
     * @var ExtensionAttributesFactory
     * @since 2.1.0
     */
    protected $extensionAttributesFactory;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param Builder $productBuilder
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Eav\Api\Data\AttributeGroupInterfaceFactory|null $attributeGroupFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Eav\Api\Data\AttributeGroupInterfaceFactory $attributeGroupFactory = null
    ) {
        parent::__construct($context, $productBuilder);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->attributeGroupFactory = $attributeGroupFactory ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Eav\Api\Data\AttributeGroupInterfaceFactory::class);
    }

    /**
     * Add attribute to attribute set
     *
     * @return \Magento\Framework\Controller\Result\Json
     * @since 2.0.0
     */
    public function execute()
    {
        $request = $this->getRequest();
        $response = new \Magento\Framework\DataObject();
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
                    throw new \Magento\Framework\Exception\NoSuchEntityException;
                }

                /** @var AttributeGroupInterface $attributeGroup */
                $attributeGroup = reset($attributeGroupItems);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
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
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.1.0
     */
    private function getBasicAttributeSearchCriteriaBuilder()
    {
        $attributeIds = (array)$this->getRequest()->getParam('attributeIds', []);

        if (empty($attributeIds['selected'])) {
            throw new LocalizedException(__('Please, specify attributes'));
        }

        return $this->getSearchCriteriaBuilder()
            ->addFilter('attribute_set_id', new \Zend_Db_Expr('null'), 'is')
            ->addFilter('attribute_id', [$attributeIds['selected']], 'in');
    }

    /**
     * @return AttributeRepositoryInterface
     * @since 2.1.0
     */
    private function getAttributeRepository()
    {
        if (null === $this->attributeRepository) {
            $this->attributeRepository = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Eav\Api\AttributeRepositoryInterface::class);
        }
        return $this->attributeRepository;
    }

    /**
     * @return AttributeSetRepositoryInterface
     * @since 2.1.0
     */
    private function getAttributeSetRepository()
    {
        if (null === $this->attributeSetRepository) {
            $this->attributeSetRepository = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Catalog\Api\AttributeSetRepositoryInterface::class);
        }
        return $this->attributeSetRepository;
    }

    /**
     * @return AttributeGroupRepositoryInterface
     * @since 2.1.0
     */
    private function getAttributeGroupRepository()
    {
        if (null === $this->attributeGroupRepository) {
            $this->attributeGroupRepository = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Eav\Api\AttributeGroupRepositoryInterface::class);
        }
        return $this->attributeGroupRepository;
    }

    /**
     * @return SearchCriteriaBuilder
     * @since 2.1.0
     */
    private function getSearchCriteriaBuilder()
    {
        if (null === $this->searchCriteriaBuilder) {
            $this->searchCriteriaBuilder = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\Api\SearchCriteriaBuilder::class);
        }
        return $this->searchCriteriaBuilder;
    }

    /**
     * @return AttributeManagementInterface
     * @since 2.1.0
     */
    private function getAttributeManagement()
    {
        if (null === $this->attributeManagement) {
            $this->attributeManagement = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Eav\Api\AttributeManagementInterface::class);
        }
        return $this->attributeManagement;
    }

    /**
     * @return LoggerInterface
     * @since 2.1.0
     */
    private function getLogger()
    {
        if (null === $this->logger) {
            $this->logger = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Psr\Log\LoggerInterface::class);
        }
        return $this->logger;
    }

    /**
     * @return ExtensionAttributesFactory
     * @since 2.1.0
     */
    private function getExtensionAttributesFactory()
    {
        if (null === $this->extensionAttributesFactory) {
            $this->extensionAttributesFactory = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\Api\ExtensionAttributesFactory::class);
        }
        return $this->extensionAttributesFactory;
    }
}
