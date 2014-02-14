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
 * @package     Magento_CatalogSearch
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog Search Controller
 *
 * @category   Magento
 * @package    Magento_CatalogSearch
 * @module     Catalog
 */
namespace Magento\CatalogSearch\Controller;

use Magento\App\Action\Context;
use Magento\CatalogSearch\Model\Advanced as ModelAdvanced;
use Magento\Session\Generic;
use Magento\UrlFactory;

class Advanced extends \Magento\App\Action\Action
{

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
     * Catalog search session
     *
     * @var Generic
     */
    protected $_catalogSearchSession;

    /**
     * Construct
     *
     * @param Context $context
     * @param Generic $catalogSearchSession
     * @param ModelAdvanced $catalogSearchAdvanced
     * @param UrlFactory $urlFactory
     */
    public function __construct(
        Context $context,
        Generic $catalogSearchSession,
        ModelAdvanced $catalogSearchAdvanced,
        UrlFactory $urlFactory
    ) {
        $this->_catalogSearchSession = $catalogSearchSession;
        $this->_catalogSearchAdvanced = $catalogSearchAdvanced;
        $this->_urlFactory = $urlFactory;
        parent::__construct($context);
    }

    /**
     * @return void
     */
    public function indexAction()
    {
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }

    /**
     * @return void
     */
    public function resultAction()
    {
        $this->_view->loadLayout();
        try {
            $this->_catalogSearchAdvanced->addFilters($this->getRequest()->getQuery());
        } catch (\Magento\Core\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $defaultUrl = $this->_urlFactory->create()
                ->setQueryParams($this->getRequest()->getQuery())
                ->getUrl('*/*/');
            $this->getResponse()->setRedirect($this->_redirect->error($defaultUrl));
        }
        $this->_view->renderLayout();
    }
}
