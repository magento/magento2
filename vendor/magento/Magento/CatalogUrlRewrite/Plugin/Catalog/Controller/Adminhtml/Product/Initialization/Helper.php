<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\CatalogUrlRewrite\Plugin\Catalog\Controller\Adminhtml\Product\Initialization;

class Helper
{
    /** @var \Magento\Framework\App\RequestInterface */
    protected $request;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
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
