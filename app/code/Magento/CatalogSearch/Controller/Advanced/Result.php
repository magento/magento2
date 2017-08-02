<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Controller\Advanced;

use Magento\Catalog\Model\Layer\Resolver;
use Magento\CatalogSearch\Model\Advanced as ModelAdvanced;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;

/**
 * Class \Magento\CatalogSearch\Controller\Advanced\Result
 *
 * @since 2.0.0
 */
class Result extends \Magento\Framework\App\Action\Action
{
    /**
     * Url factory
     *
     * @var UrlFactory
     * @since 2.0.0
     */
    protected $_urlFactory;

    /**
     * Catalog search advanced
     *
     * @var ModelAdvanced
     * @since 2.0.0
     */
    protected $_catalogSearchAdvanced;

    /**
     * Construct
     *
     * @param Context $context
     * @param ModelAdvanced $catalogSearchAdvanced
     * @param UrlFactory $urlFactory
     * @since 2.0.0
     */
    public function __construct(
        Context $context,
        ModelAdvanced $catalogSearchAdvanced,
        UrlFactory $urlFactory
    ) {
        parent::__construct($context);
        $this->_catalogSearchAdvanced = $catalogSearchAdvanced;
        $this->_urlFactory = $urlFactory;
    }

    /**
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        try {
            $this->_catalogSearchAdvanced->addFilters($this->getRequest()->getQueryValue());
            $this->_view->loadLayout();
            $this->_view->renderLayout();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
            $defaultUrl = $this->_urlFactory->create()
                ->addQueryParams($this->getRequest()->getQueryValue())
                ->getUrl('*/*/');
            $this->getResponse()->setRedirect($this->_redirect->error($defaultUrl));
        }
    }
}
