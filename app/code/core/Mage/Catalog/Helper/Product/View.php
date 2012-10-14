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
 * @category    Mage
 * @package     Mage_Catalog
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog category helper
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Helper_Product_View extends Mage_Core_Helper_Abstract
{
    // List of exceptions throwable during prepareAndRender() method
    public $ERR_NO_PRODUCT_LOADED = 1;
    public $ERR_BAD_CONTROLLER_INTERFACE = 2;

    /**
     * Path to list of session models to get messages
     */
    const XML_PATH_SESSION_MESSAGE_MODELS = 'global/session/catalog/product/message_models';

    /**
     * General config object
     *
     * @var Mage_Core_Model_Config
     */
    protected $_config;

    /**
     * Constructor dependency injection
     *
     * @param array $data
     */
    public function __construct(array $data = array())
    {
        if (isset($data['config'])) {
            $this->_config = $data['config'];
        } else {
            $this->_config = Mage::getConfig();
        }
    }

    /**
     * Inits layout for viewing product page
     *
     * @param Mage_Catalog_Model_Product $product
     * @param Mage_Core_Controller_Front_Action $controller
     *
     * @return Mage_Catalog_Helper_Product_View
     */
    public function initProductLayout($product, $controller)
    {
        $design = Mage::getSingleton('Mage_Catalog_Model_Design');
        $settings = $design->getDesignSettings($product);

        if ($settings->getCustomDesign()) {
            $design->applyCustomDesign($settings->getCustomDesign());
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
            $controller->getLayout()->helper('Mage_Page_Helper_Layout')->applyTemplate($settings->getPageLayout());
        }

        $currentCategory = Mage::registry('current_category');
        $root = $controller->getLayout()->getBlock('root');
        if ($root) {
            $controllerClass = $controller->getFullActionName();
            if ($controllerClass != 'catalog-product-view') {
                $root->addBodyClass('catalog-product-view');
            }
            $root->addBodyClass('product-' . $product->getUrlKey());
            if ($currentCategory instanceof Mage_Catalog_Model_Category) {
                $root->addBodyClass('categorypath-' . $currentCategory->getUrlPath())
                    ->addBodyClass('category-' . $currentCategory->getUrlKey());
            }
        }

        return $this;
    }

    /**
     * Prepares product view page - inits layout and all needed stuff
     *
     * $params can have all values as $params in Mage_Catalog_Helper_Product - initProduct().
     * Plus following keys:
     *   - 'buy_request' - Varien_Object holding buyRequest to configure product
     *   - 'specify_options' - boolean, whether to show 'Specify options' message
     *   - 'configure_mode' - boolean, whether we're in Configure-mode to edit product configuration
     *
     * @param int $productId
     * @param Mage_Core_Controller_Front_Action $controller
     * @param null|Varien_Object $params
     *
     * @return Mage_Catalog_Helper_Product_View
     * @throws Mage_Core_Exception
     */
    public function prepareAndRender($productId, $controller, $params = null)
    {
        // Prepare data
        $productHelper = Mage::helper('Mage_Catalog_Helper_Product');
        if (!$params) {
            $params = new Varien_Object();
        }

        // Standard algorithm to prepare and rendern product view page
        $product = $productHelper->initProduct($productId, $controller, $params);
        if (!$product) {
            throw new Mage_Core_Exception($this->__('Product is not loaded'), $this->ERR_NO_PRODUCT_LOADED);
        }

        $buyRequest = $params->getBuyRequest();
        if ($buyRequest) {
            $productHelper->prepareProductOptions($product, $buyRequest);
        }

        if ($params->hasConfigureMode()) {
            $product->setConfigureMode($params->getConfigureMode());
        }

        Mage::dispatchEvent('catalog_controller_product_view', array('product' => $product));

        if ($params->getSpecifyOptions()) {
            $notice = $product->getTypeInstance()->getSpecifyOptionMessage();
            Mage::getSingleton('Mage_Catalog_Model_Session')->addNotice($notice);
        }

        Mage::getSingleton('Mage_Catalog_Model_Session')->setLastViewedProductId($product->getId());

        $this->initProductLayout($product, $controller);

        if ($controller instanceof Mage_Catalog_Controller_Product_View_Interface) {
            foreach ($this->_getSessionMessageModels() as $sessionModel) {
                $controller->initLayoutMessages($sessionModel);
            }
        } else {
            throw new Mage_Core_Exception(
                $this->__('Bad controller interface for showing product'),
                $this->ERR_BAD_CONTROLLER_INTERFACE
            );
        }
        $controller->renderLayout();

        return $this;
    }

    /**
     * Get list of session models with messages
     *
     * @return array
     */
    protected function _getSessionMessageModels()
    {
        $messageModels = $this->_config->getNode(self::XML_PATH_SESSION_MESSAGE_MODELS)
            ->asArray();
        return array_values($messageModels);
    }
}
