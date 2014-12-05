<?php
/**
 *
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
namespace Magento\Catalog\Controller\Adminhtml\Product;

class Edit extends \Magento\Catalog\Controller\Adminhtml\Product
{

    /**
     * Array of actions which can be processed without secret key validation
     *
     * @var array
     */
    protected $_publicActions = array('edit');

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
    ) {
        parent::__construct($context, $productBuilder);
        $this->resultPageFactory = $resultPageFactory;
        $this->resultRedirectFactory = $resultRedirectFactory;
    }

    /**
     * Product edit form
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $productId = (int) $this->getRequest()->getParam('id');
        $product = $this->productBuilder->build($this->getRequest());

        if ($productId && !$product->getId()) {
            $this->messageManager->addError(__('This product no longer exists.'));
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('catalog/*/');
        }

        $this->_eventManager->dispatch('catalog_product_edit_action', array('product' => $product));

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->addHandle('catalog_product_' . $product->getTypeId());
        $resultPage->setActiveMenu('Magento_Catalog::catalog_products');
        $resultPage->getConfig()->getTitle()->prepend(__('Products'));
        $resultPage->getConfig()->getTitle()->prepend($product->getName());

        if (!$this->_objectManager->get('Magento\Framework\StoreManagerInterface')->isSingleStoreMode()
            &&
            ($switchBlock = $resultPage->getLayout()->getBlock('store_switcher'))
        ) {
            $switchBlock->setDefaultStoreName(__('Default Values'))
                ->setWebsiteIds($product->getWebsiteIds())
                ->setSwitchUrl(
                    $this->getUrl(
                        'catalog/*/*',
                        array('_current' => true, 'active_tab' => null, 'tab' => null, 'store' => null)
                    )
                );
        }

        $block = $resultPage->getLayout()->getBlock('catalog.wysiwyg.js');
        if ($block) {
            $block->setStoreId($product->getStoreId());
        }

        return $resultPage;
    }
}
