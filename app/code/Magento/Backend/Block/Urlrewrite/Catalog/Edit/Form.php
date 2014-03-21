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
 * @category    Magento
 * @package     Magento_Adminhtml
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Edit form for Catalog product and category URL rewrites
 *
 * @method \Magento\Catalog\Model\Product getProduct()
 * @method \Magento\Catalog\Model\Category getCategory()
 * @method \Magento\Backend\Block\Urlrewrite\Catalog\Edit\Form setProduct(\Magento\Catalog\Model\Product $product)
 * @method \Magento\Backend\Block\Urlrewrite\Catalog\Edit\Form setCategory(\Magento\Catalog\Model\Category $category)
 *
 * @category   Magento
 * @package    Magento_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 *
 */
namespace Magento\Backend\Block\Urlrewrite\Catalog\Edit;

/**
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Form extends \Magento\Backend\Block\Urlrewrite\Edit\Form
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
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Registry $registry
     * @param \Magento\Data\FormFactory $formFactory
     * @param \Magento\Core\Model\Source\Urlrewrite\TypesFactory $typesFactory
     * @param \Magento\Core\Model\Source\Urlrewrite\OptionsFactory $optionFactory
     * @param \Magento\Core\Model\Url\RewriteFactory $rewriteFactory
     * @param \Magento\Core\Model\System\Store $systemStore
     * @param \Magento\Backend\Helper\Data $adminhtmlData
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Catalog\Model\Url $catalogUrl
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Registry $registry,
        \Magento\Data\FormFactory $formFactory,
        \Magento\Core\Model\Source\Urlrewrite\TypesFactory $typesFactory,
        \Magento\Core\Model\Source\Urlrewrite\OptionsFactory $optionFactory,
        \Magento\Core\Model\Url\RewriteFactory $rewriteFactory,
        \Magento\Core\Model\System\Store $systemStore,
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
            $typesFactory,
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
     * @param \Magento\Data\Form $form
     * @return \Magento\Backend\Block\Urlrewrite\Catalog\Edit\Form
     */
    protected function _formPostInit($form)
    {
        // Set form action
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

        // Fill id path, request path and target path elements
        /** @var $idPath \Magento\Data\Form\Element\AbstractElement */
        $idPath = $this->getForm()->getElement('id_path');
        /** @var $requestPath \Magento\Data\Form\Element\AbstractElement */
        $requestPath = $this->getForm()->getElement('request_path');
        /** @var $targetPath \Magento\Data\Form\Element\AbstractElement */
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
                $idPath->setValue($this->_catalogUrl->generatePath('id', $product, $category));

                $sessionData = $this->_getSessionData();
                if (!isset($sessionData['request_path'])) {
                    $requestPath->setValue($this->_catalogUrl->generatePath('request', $product, $category, ''));
                }
                $targetPath->setValue($this->_catalogUrl->generatePath('target', $product, $category));
                $disablePaths = true;
            }
        } else {
            $disablePaths = $model->getProductId() || $model->getCategoryId();
        }

        // Disable id_path and target_path elements
        if ($disablePaths) {
            $idPath->setData('disabled', true);
            $targetPath->setData('disabled', true);
        }

        return $this;
    }

    /**
     * Get catalog entity associated stores
     *
     * @return array
     * @throws \Magento\Core\Model\Store\Exception
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
            // @codingStandardsIgnoreStart
            if (!$entityStores) {
                throw new \Magento\Core\Model\Store\Exception(
                    __(
                        'We can\'t set up a URL rewrite because the product you chose is not associated with a website.'
                    )
                );
            }
            $this->_requireStoresFilter = true;
        } elseif ($category->getId()) {
            $entityStores = (array)$category->getStoreIds();
            if (!$entityStores) {
                throw new \Magento\Core\Model\Store\Exception(
                    __(
                        'We can\'t set up a URL rewrite because the category your chose is not associated with a website.'
                    )
                );
            }
            $this->_requireStoresFilter = true;
        }
        // @codingStandardsIgnoreEnd

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
