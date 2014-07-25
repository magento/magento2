<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Edit form for Catalog product and category URL rewrites
 */
namespace Magento\UrlRedirect\Block\Catalog\Edit;

use Magento\UrlRedirect\Controller\Adminhtml\UrlRedirect;

/**
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Form extends \Magento\UrlRedirect\Block\Edit\Form
{
    /**
     * @var \Magento\Catalog\Model\Url
     */
    protected $_catalogUrl;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\UrlRedirect\Model\OptionProviderFactory $optionFactory
     * @param \Magento\UrlRedirect\Model\UrlRedirectFactory $rewriteFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Magento\Backend\Helper\Data $adminhtmlData
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Catalog\Model\Url $catalogUrl
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\UrlRedirect\Model\OptionProviderFactory $optionFactory,
        \Magento\UrlRedirect\Model\UrlRedirectFactory $rewriteFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Backend\Helper\Data $adminhtmlData,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\Url $catalogUrl,
        array $data = array()
    ) {
        $this->_productFactory = $productFactory;
        $this->_categoryFactory = $categoryFactory;
        $this->_catalogUrl = $catalogUrl;
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $optionFactory,
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
                array(
                    'id' => $this->_getModel()->getId(),
                    'product' => $this->_getProduct()->getId(),
                    'category' => $this->_getCategory()->getId()
                )
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
                $category = $this->_getCategory();
            } elseif ($this->_getCategory()->getId()) {
                $category = $this->_getCategory();
            }

            if ($product || $category) {
                $sessionData = $this->_getSessionData();
                if (!isset($sessionData['request_path'])) {
                    $requestPath->setValue($this->_catalogUrl->generatePath('request', $product, $category, ''));
                }
                $targetPath->setValue($this->_catalogUrl->generatePath('target', $product, $category));
                $disablePaths = true;
            }
        } else {
            $disablePaths = in_array(
                $model->getEntityType(),
                [UrlRedirect::ENTITY_TYPE_PRODUCT, UrlRedirect::ENTITY_TYPE_CATEGORY, UrlRedirect::ENTITY_TYPE_CMS_PAGE]
            );
        }

        if ($disablePaths) {
            $targetPath->setData('disabled', true);
        }

        return $this;
    }

    /**
     * Get catalog entity associated stores
     *
     * @return array
     * @throws \Magento\Store\Model\Exception
     */
    protected function _getEntityStores()
    {
        $product = $this->_getProduct();
        $category = $this->_getCategory();
        $entityStores = array();

        // showing websites that only associated to products
        if ($product->getId()) {
            $entityStores = (array)$product->getStoreIds();

            //if category is chosen, reset stores which are not related with this category
            if ($category->getId()) {
                $categoryStores = (array)$category->getStoreIds();
                $entityStores = array_intersect($entityStores, $categoryStores);
            }
            if (!$entityStores) {
                throw new \Magento\Store\Model\Exception(
                    __(
                        'We can\'t set up a URL rewrite because the product you chose is not associated with a website.'
                    )
                );
            }
            $this->_requireStoresFilter = true;
        } elseif ($category->getId()) {
            $entityStores = (array)$category->getStoreIds();
            $message = __(
                'We can\'t set up a URL rewrite because the category your chose is not associated with a website.'
            );
            if (!$entityStores) {
                throw new \Magento\Store\Model\Exception($message);
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
