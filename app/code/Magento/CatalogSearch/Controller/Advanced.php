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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
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

class Advanced extends \Magento\Core\Controller\Front\Action
{

    /**
     * Url factory
     *
     * @var \Magento\Core\Model\UrlFactory
     */
    protected $_urlFactory;

    /**
     * Catalog search advanced
     *
     * @var \Magento\CatalogSearch\Model\Advanced
     */
    protected $_catalogSearchAdvanced;

    /**
     * Catalog search session
     *
     * @var \Magento\Core\Model\Session\Generic
     */
    protected $_catalogSearchSession;

    /**
     * Construct
     *
     * @param \Magento\Core\Controller\Varien\Action\Context $context
     * @param \Magento\Core\Model\Session\Generic $catalogSearchSession
     * @param \Magento\CatalogSearch\Model\Advanced $catalogSearchAdvanced
     * @param \Magento\Core\Model\UrlFactory $urlFactory
     */
    public function __construct(
        \Magento\Core\Controller\Varien\Action\Context $context,
        \Magento\Core\Model\Session\Generic $catalogSearchSession,
        \Magento\CatalogSearch\Model\Advanced $catalogSearchAdvanced,
        \Magento\Core\Model\UrlFactory $urlFactory
    ) {
        $this->_catalogSearchSession = $catalogSearchSession;
        $this->_catalogSearchAdvanced = $catalogSearchAdvanced;
        $this->_urlFactory = $urlFactory;
        parent::__construct($context);
    }

    public function indexAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('Magento\CatalogSearch\Model\Session');
        $this->renderLayout();
    }

    public function resultAction()
    {
        $this->loadLayout();
        try {
            $this->_catalogSearchAdvanced->addFilters($this->getRequest()->getQuery());
        } catch (\Magento\Core\Exception $e) {
            $this->_catalogSearchSession->addError($e->getMessage());
            $this->_redirectError(
                $this->_urlFactory->create()
                    ->setQueryParams($this->getRequest()->getQuery())
                    ->getUrl('*/*/')
            );
        }
        $this->_initLayoutMessages('Magento\Catalog\Model\Session');
        $this->renderLayout();
    }
}
