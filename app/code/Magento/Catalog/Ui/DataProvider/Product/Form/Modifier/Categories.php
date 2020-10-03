<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\DB\Helper as DbHelper;
use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\AuthorizationInterface;
use Magento\Backend\Model\Auth\Session;

/**
 * Data provider for categories field of product page
 *
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 * @since 101.0.0
 */
class Categories extends AbstractModifier
{
    /**#@+
     * Category tree cache id
     */
    const CATEGORY_TREE_ID = 'CATALOG_PRODUCT_CATEGORY_TREE';
    /**#@-*/

    /**
     * @var CategoryCollectionFactory
     * @since 101.0.0
     */
    protected $categoryCollectionFactory;

    /**
     * @var DbHelper
     * @since 101.0.0
     */
    protected $dbHelper;

    /**
     * @var array
     * @deprecated 101.0.0
     * @since 101.0.0
     */
    protected $categoriesTrees = [];

    /**
     * @var LocatorInterface
     * @since 101.0.0
     */
    protected $locator;

    /**
     * @var UrlInterface
     * @since 101.0.0
     */
    protected $urlBuilder;

    /**
     * @var ArrayManager
     * @since 101.0.0
     */
    protected $arrayManager;

    /**
     * @var CacheInterface
     */
    private $cacheManager;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * @var Session
     */
    private $session;

    /**
     * @param LocatorInterface $locator
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param DbHelper $dbHelper
     * @param UrlInterface $urlBuilder
     * @param ArrayManager $arrayManager
     * @param SerializerInterface $serializer
     * @param AuthorizationInterface $authorization
     * @param Session $session
     */
    public function __construct(
        LocatorInterface $locator,
        CategoryCollectionFactory $categoryCollectionFactory,
        DbHelper $dbHelper,
        UrlInterface $urlBuilder,
        ArrayManager $arrayManager,
        SerializerInterface $serializer = null,
        AuthorizationInterface $authorization = null,
        Session $session = null
    ) {
        $this->locator = $locator;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->dbHelper = $dbHelper;
        $this->urlBuilder = $urlBuilder;
        $this->arrayManager = $arrayManager;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(SerializerInterface::class);
        $this->authorization = $authorization ?: ObjectManager::getInstance()->get(AuthorizationInterface::class);
        $this->session = $session ?: ObjectManager::getInstance()->get(Session::class);
    }

    /**
     * Retrieve cache interface
     *
     * @return CacheInterface
     * @deprecated 101.0.3
     */
    private function getCacheManager(): CacheInterface
    {
        if (!$this->cacheManager) {
            $this->cacheManager = ObjectManager::getInstance()
                ->get(CacheInterface::class);
        }
        return $this->cacheManager;
    }

    /**
     * @inheritdoc
     * @since 101.0.0
     */
    public function modifyMeta(array $meta)
    {
        if ($this->isAllowed()) {
            $meta = $this->createNewCategoryModal($meta);
        }
        $meta = $this->customizeCategoriesField($meta);

        return $meta;
    }

    /**
     * Check current user permission on category resource
     *
     * @return bool
     */
    private function isAllowed(): bool
    {
        return (bool) $this->authorization->isAllowed('Magento_Catalog::categories');
    }

    /**
     * @inheritdoc
     * @since 101.0.0
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * Create slide-out panel for new category creation
     *
     * @param array $meta
     * @return array
     * @since 101.0.0
     */
    protected function createNewCategoryModal(array $meta)
    {
        return $this->arrayManager->set(
            'create_category_modal',
            $meta,
            [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'isTemplate' => false,
                            'componentType' => 'modal',
                            'options' => [
                                'title' => __('New Category'),
                            ],
                            'imports' => [
                                'state' => '!index=create_category:responseStatus'
                            ],
                        ],
                    ],
                ],
                'children' => [
                    'create_category' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'label' => '',
                                    'componentType' => 'container',
                                    'component' => 'Magento_Ui/js/form/components/insert-form',
                                    'dataScope' => '',
                                    'update_url' => $this->urlBuilder->getUrl('mui/index/render'),
                                    'render_url' => $this->urlBuilder->getUrl(
                                        'mui/index/render_handle',
                                        [
                                            'handle' => 'catalog_category_create',
                                            'store' => $this->locator->getStore()->getId(),
                                            'buttons' => 1
                                        ]
                                    ),
                                    'autoRender' => false,
                                    'ns' => 'new_category_form',
                                    'externalProvider' => 'new_category_form.new_category_form_data_source',
                                    'toolbarContainer' => '${ $.parentName }',
                                    '__disableTmpl' => ['toolbarContainer' => false],
                                    'formSubmitType' => 'ajax',
                                ],
                            ],
                        ]
                    ]
                ]
            ]
        );
    }

    /**
     * Customize Categories field
     *
     * @param array $meta
     * @return array
     * @throws LocalizedException
     * @since 101.0.0
     */
    protected function customizeCategoriesField(array $meta)
    {
        $fieldCode = 'category_ids';
        $elementPath = $this->arrayManager->findPath($fieldCode, $meta, null, 'children');
        $containerPath = $this->arrayManager->findPath(static::CONTAINER_PREFIX . $fieldCode, $meta, null, 'children');
        $fieldIsDisabled = $this->locator->getProduct()->isLockedAttribute($fieldCode);

        if (!$elementPath) {
            return $meta;
        }

        $value = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => false,
                        'required' => false,
                        'dataScope' => '',
                        'breakLine' => false,
                        'formElement' => 'container',
                        'componentType' => 'container',
                        'component' => 'Magento_Ui/js/form/components/group',
                        'disabled' => $this->locator->getProduct()->isLockedAttribute($fieldCode),
                    ],
                ],
            ],
            'children' => [
                $fieldCode => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'formElement' => 'select',
                                'componentType' => 'field',
                                'component' => 'Magento_Catalog/js/components/new-category',
                                'filterOptions' => true,
                                'chipsEnabled' => true,
                                'disableLabel' => true,
                                'levelsVisibility' => '1',
                                'disabled' => $fieldIsDisabled,
                                'elementTmpl' => 'ui/grid/filters/elements/ui-select',
                                'options' => $this->getCategoriesTree(),
                                'listens' => [
                                    'index=create_category:responseData' => 'setParsed',
                                    'newOption' => 'toggleOptionSelected'
                                ],
                                'config' => [
                                    'dataScope' => $fieldCode,
                                    'sortOrder' => 10,
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        ];
        if ($this->isAllowed()) {
            $value['children']['create_category_button'] = [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'title' => __('New Category'),
                            'formElement' => 'container',
                            'additionalClasses' => 'admin__field-small',
                            'componentType' => 'container',
                            'disabled' => $fieldIsDisabled,
                            'component' => 'Magento_Ui/js/form/components/button',
                            'template' => 'ui/form/components/button/container',
                            'actions' => [
                                [
                                    'targetName' => 'product_form.product_form.create_category_modal',
                                    'actionName' => 'toggleModal',
                                ],
                                [
                                    'targetName' =>
                                        'product_form.product_form.create_category_modal.create_category',
                                    'actionName' => 'render'
                                ],
                                [
                                    'targetName' =>
                                        'product_form.product_form.create_category_modal.create_category',
                                    'actionName' => 'resetForm'
                                ]
                            ],
                            'additionalForGroup' => true,
                            'provider' => false,
                            'source' => 'product_details',
                            'displayArea' => 'insideGroup',
                            'sortOrder' => 20,
                            'dataScope'  => $fieldCode,
                        ],
                    ],
                ]
            ];
        }
        $meta = $this->arrayManager->merge($containerPath, $meta, $value);

        return $meta;
    }

    /**
     * Retrieve categories tree
     *
     * @param string|null $filter
     * @return array
     * @throws LocalizedException
     * @since 101.0.0
     */
    protected function getCategoriesTree($filter = null)
    {
        $storeId = (int) $this->locator->getStore()->getId();

        $cachedCategoriesTree = $this->getCacheManager()
            ->load($this->getCategoriesTreeCacheId($storeId, (string) $filter));
        if (!empty($cachedCategoriesTree)) {
            return $this->serializer->unserialize($cachedCategoriesTree);
        }

        $categoriesTree = $this->retrieveCategoriesTree(
            $storeId,
            $this->retrieveShownCategoriesIds($storeId, (string) $filter)
        );

        $this->getCacheManager()->save(
            $this->serializer->serialize($categoriesTree),
            $this->getCategoriesTreeCacheId($storeId, (string) $filter),
            [
                \Magento\Catalog\Model\Category::CACHE_TAG,
                \Magento\Framework\App\Cache\Type\Block::CACHE_TAG
            ]
        );

        return $categoriesTree;
    }

    /**
     * Get cache id for categories tree.
     *
     * @param int $storeId
     * @param string $filter
     * @return string
     */
    private function getCategoriesTreeCacheId(int $storeId, string $filter = ''): string
    {
        if ($this->session->getUser() !== null) {
            return self::CATEGORY_TREE_ID
                . '_' . (string)$storeId
                . '_' . $this->session->getUser()->getAclRole()
                . '_' . $filter;
        }
        return self::CATEGORY_TREE_ID
            . '_' . (string)$storeId
            . '_' . $filter;
    }

    /**
     * Retrieve filtered list of categories id.
     *
     * @param int $storeId
     * @param string $filter
     * @return array
     * @throws LocalizedException
     */
    private function retrieveShownCategoriesIds(int $storeId, string $filter = '') : array
    {
        /* @var $matchingNamesCollection \Magento\Catalog\Model\ResourceModel\Category\Collection */
        $matchingNamesCollection = $this->categoryCollectionFactory->create();

        if (!empty($filter)) {
            $matchingNamesCollection->addAttributeToFilter(
                'name',
                ['like' => $this->dbHelper->addLikeEscape($filter, ['position' => 'any'])]
            );
        }

        $matchingNamesCollection->addAttributeToSelect('path')
            ->addAttributeToFilter('entity_id', ['neq' => CategoryModel::TREE_ROOT_ID])
            ->setStoreId($storeId);

        $shownCategoriesIds = [];

        /** @var \Magento\Catalog\Model\Category $category */
        foreach ($matchingNamesCollection as $category) {
            foreach (explode('/', $category->getPath()) as $parentId) {
                $shownCategoriesIds[$parentId] = 1;
            }
        }

        return $shownCategoriesIds;
    }

    /**
     * Retrieve tree of categories with attributes.
     *
     * @param int $storeId
     * @param array $shownCategoriesIds
     * @return array|null
     * @throws LocalizedException
     */
    private function retrieveCategoriesTree(int $storeId, array $shownCategoriesIds) : ?array
    {
        /* @var $collection \Magento\Catalog\Model\ResourceModel\Category\Collection */
        $collection = $this->categoryCollectionFactory->create();

        $collection->addAttributeToFilter('entity_id', ['in' => array_keys($shownCategoriesIds)])
            ->addAttributeToSelect(['name', 'is_active', 'parent_id'])
            ->setStoreId($storeId);

        $categoryById = [
            CategoryModel::TREE_ROOT_ID => [
                'value' => CategoryModel::TREE_ROOT_ID,
                'optgroup' => null,
            ],
        ];

        foreach ($collection as $category) {
            foreach ([$category->getId(), $category->getParentId()] as $categoryId) {
                if (!isset($categoryById[$categoryId])) {
                    $categoryById[$categoryId] = ['value' => $categoryId];
                }
            }

            $categoryById[$category->getId()]['is_active'] = $category->getIsActive();
            $categoryById[$category->getId()]['label'] = $category->getName();
            $categoryById[$category->getId()]['__disableTmpl'] = true;
            $categoryById[$category->getParentId()]['optgroup'][] = &$categoryById[$category->getId()];
        }

        return $categoryById[CategoryModel::TREE_ROOT_ID]['optgroup'];
    }
}
