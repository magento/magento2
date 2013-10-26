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
 * @package     Magento_Catalog
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog category helper
 *
 * @category    Magento
 * @package     Magento_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Helper\Product;

class View extends \Magento\Core\Helper\AbstractHelper
{
    // List of exceptions throwable during prepareAndRender() method
    public $ERR_NO_PRODUCT_LOADED = 1;
    public $ERR_BAD_CONTROLLER_INTERFACE = 2;

    /**
     * List of catalog product session message models name
     *
     * @var array
     */
    protected $_messageModels;

    /**
     * Core registry
     *
     * @var \Magento\Core\Model\Registry
     */
    protected $_coreRegistry = null;
    
    /**
     * Catalog product
     *
     * @var \Magento\Catalog\Helper\Product
     */
    protected $_catalogProduct = null;

    /**
     * Catalog product
     *
     * @var \Magento\Page\Helper\Layout
     */
    protected $_pageLayout = null;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Event\ManagerInterface
     */
    protected $_eventManager = null;

    /**
     * Catalog design
     *
     * @var \Magento\Catalog\Model\Design
     */
    protected $_catalogDesign;

    /**
     * Catalog session
     *
     * @var \Magento\Catalog\Model\Session
     */
    protected $_catalogSession;

    /**
     * Construct
     *
     * @param \Magento\Catalog\Model\Session $catalogSession
     * @param \Magento\Catalog\Model\Design $catalogDesign
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\Catalog\Helper\Product $catalogProduct
     * @param \Magento\Page\Helper\Layout $pageLayout
     * @param \Magento\Core\Helper\Context $context
     * @param \Magento\Core\Model\Registry $coreRegistry
     * @param array $messageModels
     */
    public function __construct(
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Catalog\Model\Design $catalogDesign,
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\Catalog\Helper\Product $catalogProduct,
        \Magento\Page\Helper\Layout $pageLayout,
        \Magento\Core\Helper\Context $context,
        \Magento\Core\Model\Registry $coreRegistry,
        array $messageModels = array()
    ) {
        $this->_catalogSession = $catalogSession;
        $this->_catalogDesign = $catalogDesign;
        $this->_eventManager = $eventManager;
        $this->_catalogProduct = $catalogProduct;
        $this->_pageLayout = $pageLayout;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
        $this->_messageModels = $messageModels;
    }

    /**
     * Inits layout for viewing product page
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Core\Controller\Front\Action $controller
     *
     * @return \Magento\Catalog\Helper\Product\View
     */
    public function initProductLayout($product, $controller)
    {
        $settings = $this->_catalogDesign->getDesignSettings($product);

        if ($settings->getCustomDesign()) {
            $this->_catalogDesign->applyCustomDesign($settings->getCustomDesign());
        }

        $update = $controller->getLayout()->getUpdate();
        $controller->addPageLayoutHandles(
            array('id' => $product->getId(), 'sku' => $product->getSku(), 'type' => $product->getTypeId())
        );
        $controller->loadLayoutUpdates();
        // Apply custom layout update once layout is loaded
        $layoutUpdates = $settings->getLayoutUpdates();
        if ($layoutUpdates) {
            if (is_array($layoutUpdates)) {
                foreach ($layoutUpdates as $layoutUpdate) {
                    $update->addUpdate($layoutUpdate);
                }
            }
        }

        $controller->generateLayoutXml()->generateLayoutBlocks();

        // Apply custom layout (page) template once the blocks are generated
        if ($settings->getPageLayout()) {
            $this->_pageLayout->applyTemplate($settings->getPageLayout());
        }

        $currentCategory = $this->_coreRegistry->registry('current_category');
        $root = $controller->getLayout()->getBlock('root');
        if ($root) {
            $controllerClass = $controller->getFullActionName();
            if ($controllerClass != 'catalog-product-view') {
                $root->addBodyClass('catalog-product-view');
            }
            $root->addBodyClass('product-' . $product->getUrlKey());
            if ($currentCategory instanceof \Magento\Catalog\Model\Category) {
                $root->addBodyClass('categorypath-' . $currentCategory->getUrlPath())
                    ->addBodyClass('category-' . $currentCategory->getUrlKey());
            }
        }

        return $this;
    }

    /**
     * Prepares product view page - inits layout and all needed stuff
     *
     * $params can have all values as $params in \Magento\Catalog\Helper\Product - initProduct().
     * Plus following keys:
     *   - 'buy_request' - \Magento\Object holding buyRequest to configure product
     *   - 'specify_options' - boolean, whether to show 'Specify options' message
     *   - 'configure_mode' - boolean, whether we're in Configure-mode to edit product configuration
     *
     * @param int $productId
     * @param \Magento\Core\Controller\Front\Action $controller
     * @param null|\Magento\Object $params
     *
     * @return \Magento\Catalog\Helper\Product\View
     * @throws \Magento\Core\Exception
     */
    public function prepareAndRender($productId, $controller, $params = null)
    {
        // Prepare data
        $productHelper = $this->_catalogProduct;
        if (!$params) {
            $params = new \Magento\Object();
        }

        // Standard algorithm to prepare and rendern product view page
        $product = $productHelper->initProduct($productId, $controller, $params);
        if (!$product) {
            throw new \Magento\Core\Exception(__('Product is not loaded'), $this->ERR_NO_PRODUCT_LOADED);
        }

        $buyRequest = $params->getBuyRequest();
        if ($buyRequest) {
            $productHelper->prepareProductOptions($product, $buyRequest);
        }

        if ($params->hasConfigureMode()) {
            $product->setConfigureMode($params->getConfigureMode());
        }

        $this->_eventManager->dispatch('catalog_controller_product_view', array('product' => $product));

        if ($params->getSpecifyOptions()) {
            $notice = $product->getTypeInstance()->getSpecifyOptionMessage();
            $this->_catalogSession->addNotice($notice);
        }

        $this->_catalogSession->setLastViewedProductId($product->getId());

        $this->initProductLayout($product, $controller);

        if ($controller instanceof \Magento\Catalog\Controller\Product\View\ViewInterface) {
            foreach ($this->_messageModels as $sessionModel) {
                $controller->initLayoutMessages($sessionModel);
            }
        } else {
            throw new \Magento\Core\Exception(
                __('Bad controller interface for showing product'),
                $this->ERR_BAD_CONTROLLER_INTERFACE
            );
        }
        $controller->renderLayout();

        return $this;
    }
}
