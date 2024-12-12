<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Edit form for Catalog product and category URL rewrites
 */
namespace Magento\UrlRewrite\Block\Catalog\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Helper\Data as BackendHelper;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use Magento\Framework\Data\Form as FormData;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store as SystemStore;
use Magento\UrlRewrite\Block\Edit\Form as UrlRewriteEditForm;
use Magento\UrlRewrite\Controller\Adminhtml\Url\Rewrite;
use Magento\UrlRewrite\Model\OptionProvider;
use Magento\UrlRewrite\Model\UrlRewriteFactory;

/**
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Form extends UrlRewriteEditForm
{
    /**
     * @var ProductFactory
     */
    protected $_productFactory;

    /**
     * @var CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param OptionProvider $optionProvider
     * @param UrlRewriteFactory $rewriteFactory
     * @param SystemStore $systemStore
     * @param BackendHelper $adminhtmlData
     * @param ProductFactory $productFactory
     * @param CategoryFactory $categoryFactory
     * @param ProductUrlPathGenerator $productUrlPathGenerator
     * @param CategoryUrlPathGenerator $categoryUrlPathGenerator
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        OptionProvider $optionProvider,
        UrlRewriteFactory $rewriteFactory,
        SystemStore $systemStore,
        BackendHelper $adminhtmlData,
        ProductFactory $productFactory,
        CategoryFactory $categoryFactory,
        protected readonly ProductUrlPathGenerator $productUrlPathGenerator,
        protected readonly CategoryUrlPathGenerator $categoryUrlPathGenerator,
        array $data = []
    ) {
        $this->_productFactory = $productFactory;
        $this->_categoryFactory = $categoryFactory;
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $optionProvider,
            $rewriteFactory,
            $systemStore,
            $adminhtmlData,
            $data
        );
    }

    /**
     * Form post init
     *
     * @param FormData $form
     * @return $this
     */
    protected function _formPostInit($form)
    {
        $form->setAction(
            $this->_adminhtmlData->getUrl(
                'adminhtml/*/save',
                [
                    'id' => $this->_getModel()->getId(),
                    'product' => $this->_getProduct()->getId(),
                    'category' => $this->_getCategory()->getId()
                ]
            )
        );

        /** @var AbstractElement $requestPath */
        $requestPath = $this->getForm()->getElement('request_path');
        /** @var AbstractElement $targetPath */
        $targetPath = $this->getForm()->getElement('target_path');

        $model = $this->_getModel();
        $disablePaths = false;
        if (!$model->getId()) {
            $product = null;
            $category = null;
            if ($this->_getProduct()->getId()) {
                $product = $this->_getProduct();
            }
            if ($this->_getCategory()->getId()) {
                $category = $this->_getCategory();
            }

            if ($product || $category) {
                $sessionData = $this->_getSessionData();
                if (!isset($sessionData['request_path'])) {
                    $requestPath->setValue($this->getRequestPath($product, $category));
                }
                $targetPath->setValue($this->getTargetPath($product, $category));
                $disablePaths = true;
            }
        } else {
            $disablePaths = in_array(
                $model->getEntityType(),
                [Rewrite::ENTITY_TYPE_PRODUCT, Rewrite::ENTITY_TYPE_CATEGORY, Rewrite::ENTITY_TYPE_CMS_PAGE]
            );
        }

        if ($disablePaths) {
            $targetPath->setData('disabled', true);
        }

        return $this;
    }

    /**
     * @param Product|null $product
     * @param Category|null $category
     * @return string
     */
    protected function getRequestPath($product = null, $category = null)
    {
        return $product
            ? $this->productUrlPathGenerator->getUrlPathWithSuffix($product, $product->getStoreId(), $category)
            : $this->categoryUrlPathGenerator->getUrlPathWithSuffix($category);
    }

    /**
     * @param Product|null $product
     * @param Category|null $category
     * @return string
     */
    protected function getTargetPath($product = null, $category = null)
    {
        return $product
            ? $this->productUrlPathGenerator->getCanonicalUrlPath($product, $category)
            : $this->categoryUrlPathGenerator->getCanonicalUrlPath($category);
    }

    /**
     * Get catalog entity associated stores
     *
     * @return array
     * @throws LocalizedException
     */
    protected function _getEntityStores()
    {
        $product = $this->_getProduct();
        $category = $this->_getCategory();
        $entityStores = [];

        // showing websites that only associated to products
        if ($product->getId()) {
            $entityStores = (array)$product->getStoreIds();

            //if category is chosen, reset stores which are not related with this category
            if ($category->getId()) {
                $categoryStores = (array)$category->getStoreIds();
                $entityStores = array_intersect($entityStores, $categoryStores);
            }
            if (!$entityStores) {
                throw new LocalizedException(
                    __(
                        'We can\'t set up a URL rewrite because the product you chose is not associated with a website.'
                    )
                );
            }
            $this->_requireStoresFilter = true;
        } elseif ($category->getId()) {
            $entityStores = (array)$category->getStoreIds();
            $message = __(
                'Please assign a website to the selected category.'
            );
            if (!$entityStores) {
                throw new LocalizedException($message);
            }
            $this->_requireStoresFilter = true;
        }

        return $entityStores;
    }

    /**
     * Get product model instance
     *
     * @return Product
     */
    protected function _getProduct()
    {
        if (!$this->hasData('product')) {
            $this->setProduct($this->_productFactory->create());
        }
        return $this->getProduct();
    }

    /**
     * Get category model instance
     *
     * @return Category
     */
    protected function _getCategory()
    {
        if (!$this->hasData('category')) {
            $this->setCategory($this->_categoryFactory->create());
        }
        return $this->getCategory();
    }
}
