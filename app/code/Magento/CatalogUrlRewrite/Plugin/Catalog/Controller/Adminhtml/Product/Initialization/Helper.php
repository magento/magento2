<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Plugin\Catalog\Controller\Adminhtml\Product\Initialization;

/**
 * Class \Magento\CatalogUrlRewrite\Plugin\Catalog\Controller\Adminhtml\Product\Initialization\Helper
 *
 * @since 2.0.0
 */
class Helper
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     * @since 2.0.0
     */
    protected $request;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->request = $request;
    }

    /**
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper $subject
     * @param \Magento\Catalog\Model\Product $result
     * @return \Magento\Catalog\Model\Product
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function afterInitialize(
        \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper $subject,
        \Magento\Catalog\Model\Product $result
    ) {
        $productData = $this->request->getPost('product');
        /**
         * Create Permanent Redirect for old URL key
         */
        if ($result->getId() && isset($productData['url_key_create_redirect'])) {
            $result->setData('save_rewrites_history', (bool)$productData['url_key_create_redirect']);
        }
        return $result;
    }
}
