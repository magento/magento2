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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Controller;

/**
 * Category controller
 *
 * @category   Magento
 * @package    Magento_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Category extends \Magento\App\Action\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Catalog session
     *
     * @var \Magento\Catalog\Model\Session
     */
    protected $_catalogSession;

    /**
     * Catalog design
     *
     * @var \Magento\Catalog\Model\Design
     */
    protected $_catalogDesign;

    /**
     * Category factory
     *
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\App\Action\Context $context
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Catalog\Model\Design $catalogDesign
     * @param \Magento\Catalog\Model\Session $catalogSession
     * @param \Magento\Registry $coreRegistry
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\App\Action\Context $context,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\Design $catalogDesign,
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Registry $coreRegistry,
        \Magento\Core\Model\StoreManagerInterface $storeManager
    ) {
        $this->_storeManager = $storeManager;
        $this->_categoryFactory = $categoryFactory;
        $this->_catalogDesign = $catalogDesign;
        $this->_catalogSession = $catalogSession;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Initialize requested category object
     *
     * @return \Magento\Catalog\Model\Category
     */
    protected function _initCategory()
    {
        $categoryId = (int)$this->getRequest()->getParam('id', false);
        if (!$categoryId) {
            return false;
        }

        /** @var \Magento\Catalog\Model\Category $category */
        $category = $this->_categoryFactory->create()->setStoreId(
            $this->_storeManager->getStore()->getId()
        )->load(
            $categoryId
        );

        if (!$this->_objectManager->get('Magento\Catalog\Helper\Category')->canShow($category)) {
            return false;
        }
        $this->_catalogSession->setLastVisitedCategoryId($category->getId());
        $this->_coreRegistry->register('current_category', $category);
        try {
            $this->_eventManager->dispatch(
                'catalog_controller_category_init_after',
                array('category' => $category, 'controller_action' => $this)
            );
        } catch (\Magento\Model\Exception $e) {
            $this->_objectManager->get('Magento\Logger')->logException($e);
            return false;
        }

        return $category;
    }

    /**
     * Category view action
     *
     * @return void
     */
    public function viewAction()
    {
        if ($this->_request->getParam(\Magento\App\Action\Action::PARAM_NAME_URL_ENCODED)) {
            $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl());
            return;
        }
        $category = $this->_initCategory();
        if ($category) {
            $settings = $this->_catalogDesign->getDesignSettings($category);

            // apply custom design
            if ($settings->getCustomDesign()) {
                $this->_catalogDesign->applyCustomDesign($settings->getCustomDesign());
            }

            $this->_catalogSession->setLastViewedCategoryId($category->getId());

            $update = $this->_view->getLayout()->getUpdate();
            $update->addHandle('default');
            if ($category->getIsAnchor()) {
                $type = $category->hasChildren() ? 'layered' : 'layered_without_children';
            } else {
                $type = $category->hasChildren() ? 'default' : 'default_without_children';
            }

            if (!$category->hasChildren()) {
                // Two levels removed from parent.  Need to add default page type.
                $parentType = strtok($type, '_');
                $this->_view->addPageLayoutHandles(array('type' => $parentType));
            }
            $this->_view->addPageLayoutHandles(array('type' => $type, 'id' => $category->getId()));
            $this->_view->loadLayoutUpdates();

            // apply custom layout update once layout is loaded
            $layoutUpdates = $settings->getLayoutUpdates();
            if ($layoutUpdates && is_array($layoutUpdates)) {
                foreach ($layoutUpdates as $layoutUpdate) {
                    $update->addUpdate($layoutUpdate);
                }
            }

            $this->_view->generateLayoutXml();
            $this->_view->generateLayoutBlocks();
            // apply custom layout (page) template once the blocks are generated
            if ($settings->getPageLayout()) {
                $this->_objectManager->get('Magento\Theme\Helper\Layout')->applyTemplate($settings->getPageLayout());
            }

            $root = $this->_view->getLayout()->getBlock('root');
            if ($root) {
                $root->addBodyClass(
                    'categorypath-' . $category->getUrlPath()
                )->addBodyClass(
                    'category-' . $category->getUrlKey()
                );
            }

            $this->_view->getLayout()->initMessages();
            $this->_view->renderLayout();
        } elseif (!$this->getResponse()->isRedirect()) {
            $this->_forward('noroute');
        }
    }
}
