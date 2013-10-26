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
 * @package     Magento_Rss
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Customer reviews controller
 */
namespace Magento\Rss\Controller;

class Catalog extends \Magento\Core\Controller\Front\Action
{
    /**
     * @var \Magento\Config\Scope
     */
    protected $_configScope;

    /**
     * @var \Magento\Core\Model\Store\Config
     */
    protected $_storeConfig;

    /**
     * @var \Magento\Core\Model\Logger
     */
    protected $_logger;

    /**
     * @param \Magento\Core\Controller\Varien\Action\Context $context
     * @param \Magento\Config\Scope $configScope
     * @param \Magento\Core\Model\Store\Config $storeConfig
     */
    public function __construct(
        \Magento\Core\Controller\Varien\Action\Context $context,
        \Magento\Config\Scope $configScope,
        \Magento\Core\Model\Store\Config $storeConfig
    ) {
        $this->_configScope = $configScope;
        $this->_storeConfig = $storeConfig;
        $this->_logger = $context->getLogger();
        parent::__construct($context);
    }

    /**
     * Emulate admin area for certain actions
     */
    public function preDispatch()
    {
        $action = $this->getRequest()->getActionName();
        /**
         * Format actionName => acrResourceId
         */
        $acl = array(
            'notifystock' => 'Magento_Catalog::products',
            'review' => 'Magento_Review::reviews_all',
        );
        if (isset($acl[$action])) {
            $this->_configScope->setCurrentScope(\Magento\Core\Model\App\Area::AREA_ADMINHTML);
            if ($this->authenticateAndAuthorizeAdmin($acl[$action], $this->_logger)) {
                return;
            }
        }
        parent::preDispatch();
    }

    public function newAction()
    {
        $this->_genericAction('new');
    }

    public function specialAction()
    {
        $this->_genericAction('special');
    }

    public function salesruleAction()
    {
        $this->_genericAction('salesrule');
    }

    public function notifystockAction()
    {
        $this->_render();
    }

    public function reviewAction()
    {
        $this->_render();
    }

    public function categoryAction()
    {
         $this->_genericAction('category');
    }

    /**
     * Render or forward to "no route" action if this type of RSS is disabled
     *
     * @param string $code
     */
    protected function _genericAction($code)
    {
        if ($this->_isEnabled($code)) {
            $this->_render();
        } else {
            $this->_forward('nofeed', 'index', 'rss');
        }
    }

    /**
     * Whether specified type of RSS is enabled
     *
     * @param string $code
     * @return bool
     */
    protected function _isEnabled($code)
    {
        return $this->_storeConfig->getConfigFlag("rss/catalog/{$code}");
    }

    /**
     * Render as XML-document using layout handle without inheriting any other handles
     */
    protected function _render()
    {
        $this->getResponse()->setHeader('Content-Type', 'text/xml; charset=UTF-8');
        $this->loadLayout(false);
        $this->renderLayout();
    }
}
