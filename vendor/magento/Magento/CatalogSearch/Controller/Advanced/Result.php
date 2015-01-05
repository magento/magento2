<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\CatalogSearch\Controller\Advanced;

use Magento\Catalog\Model\Layer\Resolver;
use Magento\CatalogSearch\Model\Advanced as ModelAdvanced;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;

class Result extends \Magento\Framework\App\Action\Action
{
    /**
     * Catalog Layer Resolver
     *
     * @var Resolver
     */
    private $layerResolver;

    /**
     * Url factory
     *
     * @var UrlFactory
     */
    protected $_urlFactory;

    /**
     * Catalog search advanced
     *
     * @var ModelAdvanced
     */
    protected $_catalogSearchAdvanced;

    /**
     * Construct
     *
     * @param Context $context
     * @param ModelAdvanced $catalogSearchAdvanced
     * @param UrlFactory $urlFactory
     * @param Resolver $layerResolver
     */
    public function __construct(
        Context $context,
        ModelAdvanced $catalogSearchAdvanced,
        UrlFactory $urlFactory,
        Resolver $layerResolver
    ) {
        parent::__construct($context);
        $this->_catalogSearchAdvanced = $catalogSearchAdvanced;
        $this->_urlFactory = $urlFactory;
        $this->layerResolver = $layerResolver;
    }

    /**
     * @return void
     */
    public function execute()
    {
        try {
            $this->layerResolver->create('advanced');
            $this->_catalogSearchAdvanced->addFilters($this->getRequest()->getQuery());
            $this->_view->loadLayout();
            $this->_view->renderLayout();
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $defaultUrl = $this->_urlFactory->create()
                ->addQueryParams($this->getRequest()->getQuery())
                ->getUrl('*/*/');
            $this->getResponse()->setRedirect($this->_redirect->error($defaultUrl));
        }
    }
}
