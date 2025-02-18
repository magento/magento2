<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */

namespace Magento\Catalog\Model;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category\Attribute\LayoutUpdateManager as CategoryLayoutManager;
use Magento\Catalog\Model\Product\Attribute\LayoutUpdateManager as ProductLayoutManager;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\TranslateInterface;
use Magento\Framework\View\DesignInterface;

/**
 * Catalog Custom Category design Model
 *
 * @api
 *
 * @since 100.0.2
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Design extends \Magento\Framework\Model\AbstractModel
{
    public const APPLY_FOR_PRODUCT = 1;

    public const APPLY_FOR_CATEGORY = 2;

    /**
     * Design package instance
     *
     * @var DesignInterface
     */
    protected $_design = null;

    /**
     * @var TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var TranslateInterface
     */
    private $translator;

    /**
     * @var CategoryLayoutManager
     */
    private $categoryLayoutUpdates;

    /**
     * @var ProductLayoutManager
     */
    private $productLayoutUpdates;

    /**
     * @var Session
     */
    private $catalogSession;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param TimezoneInterface $localeDate
     * @param DesignInterface $design
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     * @param TranslateInterface|null $translator
     * @param CategoryLayoutManager|null $categoryLayoutManager
     * @param ProductLayoutManager|null $productLayoutManager
     * @param Session|null $catalogSession
     * @param CategoryRepositoryInterface|null $categoryRepository
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $registry,
        TimezoneInterface $localeDate,
        DesignInterface $design,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = [],
        ?TranslateInterface $translator = null,
        ?CategoryLayoutManager $categoryLayoutManager = null,
        ?ProductLayoutManager $productLayoutManager = null,
        ?Session $catalogSession = null,
        ?CategoryRepositoryInterface $categoryRepository = null
    ) {
        $this->_localeDate = $localeDate;
        $this->_design = $design;
        $this->translator = $translator ?? ObjectManager::getInstance()->get(TranslateInterface::class);
        $this->categoryLayoutUpdates = $categoryLayoutManager
            ?? ObjectManager::getInstance()->get(CategoryLayoutManager::class);
        $this->productLayoutUpdates = $productLayoutManager
            ?? ObjectManager::getInstance()->get(ProductLayoutManager::class);
        $this->catalogSession = $catalogSession
            ?? ObjectManager::getInstance()->get(Session::class);
        $this->categoryRepository = $categoryRepository
            ?? ObjectManager::getInstance()->get(CategoryRepositoryInterface::class);
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Apply custom design
     *
     * @param string $design
     * @return $this
     */
    public function applyCustomDesign($design)
    {
        $this->_design->setDesignTheme($design);
        $this->translator->loadData(null, true);
        return $this;
    }

    /**
     * Get custom layout settings
     *
     * @param Category|Product $object
     * @return DataObject
     */
    public function getDesignSettings($object)
    {
        if ($object instanceof Product) {
            $currentCategory = $object->getCategory();
            if (!$currentCategory) {
                $lastId = $this->catalogSession->getLastVisitedCategoryId();
                if ($object->canBeShowInCategory($lastId)) {
                    $categoryId = $lastId;
                    try {
                        $currentCategory = $this->categoryRepository->get($categoryId);
                    } catch (NoSuchEntityException $e) {
                        $currentCategory = null;
                    }
                }
            }
        } else {
            $currentCategory = $object;
        }

        $category = null;
        if ($currentCategory) {
            $category = $currentCategory->getParentDesignCategory($currentCategory);
        }

        if ($object instanceof Product) {
            if ($category && $category->getCustomApplyToProducts()) {
                return $this->_mergeSettings($this->_extractSettings($category), $this->_extractSettings($object));
            } else {
                return $this->_extractSettings($object);
            }
        } else {
            return $this->_extractSettings($category);
        }
    }

    /**
     * Extract custom layout settings from category or product object
     *
     * @param Category|Product $object
     * @return DataObject
     */
    protected function _extractSettings($object)
    {
        $settings = new DataObject();
        if (!$object) {
            return $settings;
        }
        $settings->setPageLayout($object->getPageLayout());
        $settings->setLayoutUpdates((array)$object->getCustomLayoutUpdate());

        $date = $object->getCustomDesignDate();
        if (array_key_exists(
            'from',
            $date
        ) && array_key_exists(
            'to',
            $date
        ) && $this->_localeDate->isScopeDateInInterval(
            null,
            $date['from'],
            $date['to']
        )
        ) {
            if ($object->getCustomDesign()) {
                $settings->setCustomDesign($object->getCustomDesign());
            }
            if ($object->getCustomLayout()) {
                $settings->setPageLayout($object->getCustomLayout());
            }
            if ($object instanceof Category) {
                $this->categoryLayoutUpdates->extractCustomSettings($object, $settings);
            } elseif ($object instanceof Product) {
                $this->productLayoutUpdates->extractCustomSettings($object, $settings);
            }
        }

        return $settings;
    }

    /**
     * Merge custom design settings
     *
     * @param DataObject $categorySettings
     * @param DataObject $productSettings
     * @return DataObject
     */
    protected function _mergeSettings($categorySettings, $productSettings)
    {
        if ($productSettings->getCustomDesign()) {
            $categorySettings->setCustomDesign($productSettings->getCustomDesign());
        }
        if ($productSettings->getPageLayout()) {
            $categorySettings->setPageLayout($productSettings->getPageLayout());
        }
        if ($productSettings->getLayoutUpdates()) {
            $update = array_merge($categorySettings->getLayoutUpdates(), $productSettings->getLayoutUpdates());
            $categorySettings->setLayoutUpdates($update);
        }
        if ($categorySettings->getPageLayoutHandles()) {
            $handles = [];
            foreach ($categorySettings->getPageLayoutHandles() as $key => $value) {
                $handles[$key] = [
                    'handle' => 'catalog_category_view',
                    'value' => $value,
                ];
            }
            $categorySettings->setPageLayoutHandles($handles);
        }
        if ($productSettings->getPageLayoutHandles()) {
            $handle = array_merge($categorySettings->getPageLayoutHandles(), $productSettings->getPageLayoutHandles());
            $categorySettings->setPageLayoutHandles($handle);
        }

        return $categorySettings;
    }
}
