<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product;

/**
 * Class \Magento\Catalog\Controller\Adminhtml\Product\Wysiwyg
 *
 * @since 2.0.0
 */
class Wysiwyg extends \Magento\Catalog\Controller\Adminhtml\Product
{
    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     * @since 2.0.0
     */
    protected $resultRawFactory;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     * @since 2.0.0
     */
    protected $layoutFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory
    ) {
        parent::__construct($context, $productBuilder);
        $this->resultRawFactory = $resultRawFactory;
        $this->layoutFactory = $layoutFactory;
    }

    /**
     * WYSIWYG editor action for ajax request
     *
     * @return \Magento\Framework\Controller\Result\Raw
     * @since 2.0.0
     */
    public function execute()
    {
        $elementId = $this->getRequest()->getParam('element_id', md5(microtime()));
        $storeId = $this->getRequest()->getParam('store_id', 0);
        $storeMediaUrl = $this->_objectManager->get(\Magento\Store\Model\StoreManagerInterface::class)
            ->getStore($storeId)
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        $content = $this->layoutFactory->create()
            ->createBlock(
                \Magento\Catalog\Block\Adminhtml\Helper\Form\Wysiwyg\Content::class,
                '',
                [
                    'data' => [
                        'editor_element_id' => $elementId,
                        'store_id' => $storeId,
                        'store_media_url' => $storeMediaUrl,
                    ]
                ]
            );

        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();
        return $resultRaw->setContents($content->toHtml());
    }
}
