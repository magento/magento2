<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Edit form for Catalog product and category URL rewrites
 */
namespace Magento\UrlRewrite\Block\Catalog\Edit;

use Magento\UrlRewrite\Controller\Adminhtml\Url\Rewrite;

/**
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Form extends \Magento\UrlRewrite\Block\Edit\Form
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator
     */
    protected $productUrlPathGenerator;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator
     */
    protected $categoryUrlPathGenerator;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\UrlRewrite\Model\OptionProvider $optionProvider
     * @param \Magento\UrlRewrite\Model\UrlRewriteFactory $rewriteFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Magento\Backend\Helper\Data $adminhtmlData
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator $productUrlPathGenerator
     * @param \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator $categoryUrlPathGenerator
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\UrlRewrite\Model\OptionProvider $optionProvider,
        \Magento\UrlRewrite\Model\UrlRewriteFactory $rewriteFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Backend\Helper\Data $adminhtmlData,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator $productUrlPathGenerator,
        \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator $categoryUrlPathGenerator,
        array $data = []
    ) {
        $this->_productFactory = $productFactory;
        $this->_categoryFactory = $categoryFactory;
        $this->productUrlPathGenerator = $productUrlPathGenerator;
        $this->categoryUrlPathGenerator = $categoryUrlPathGenerator;
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
     * @param \Magento\Framework\Data\Form $form
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

        /** @var $requestPath \Magento\Framework\Data\Form\Element\AbstractElement */
        $requestPath = $this->getForm()->getElement('request_path');
        /** @var $targetPath \Magento\Framework\Data\Form\Element\AbstractElement */
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
     * @param \Magento\Catalog\Model\Product|null $product
     * @param \Magento\Catalog\Model\Category|null $category
     * @return string
     */
    protected function getRequestPath($product = null, $category = null)
    {
        return $product
            ? $this->productUrlPathGenerator->getUrlPathWithSuffix($product, $product->getStoreId(), $category)
            : $this->categoryUrlPathGenerator->getUrlPathWithSuffix($category);
    }

    /**
     * @param \Magento\Catalog\Model\Product|null $product
     * @param \Magento\Catalog\Model\Category|null $category
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
     * @throws \Magento\Framework\Exception\LocalizedException
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
                throw new \Magento\Framework\Exception\LocalizedException(
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
                throw new \Magento\Framework\Exception\LocalizedException($message);
            }
            $this->_requireStoresFilter = true;
        }

        return $entityStores;
    }

    /**
     * Get product model instance
     *
     * @return \Magento\Catalog\Model\Product
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
     * @return \Magento\Catalog\Model\Category
     */
    protected function _getCategory()
    {
        if (!$this->hasData('category')) {
            $this->setCategory($this->_categoryFactory->create());
        }
        return $this->getCategory();
    }
}
