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
 * @category    Mage
 * @package     Mage_Rss
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Customer reviews controller
 *
 * @category   Mage
 * @package    Mage_Rss
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Rss_CatalogController extends Mage_Core_Controller_Front_Action
{
    protected function isFeedEnable($code)
    {
        return Mage::getStoreConfig('rss/catalog/'.$code);
    }

    protected function checkFeedEnable($code)
    {
        if ($this->isFeedEnable($code)) {
            $this->getResponse()->setHeader('Content-type', 'text/xml; charset=UTF-8');
            return true;
        } else {
            $this->getResponse()->setHeader('HTTP/1.1','404 Not Found');
            $this->getResponse()->setHeader('Status','404 File not found');
            $this->_forward('nofeed','index','rss');
            return false;
        }
    }

    public function newAction()
    {
        $this->checkFeedEnable('new');
        $this->loadLayout(false);
        $this->renderLayout();
    }

    public function specialAction()
    {
        $this->checkFeedEnable('special');
        $this->loadLayout(false);
        $this->renderLayout();
    }

    public function salesruleAction()
    {
        $this->checkFeedEnable('salesrule');
        $this->loadLayout(false);
        $this->renderLayout();
    }

    public function tagAction()
    {
        if ($this->checkFeedEnable('tag')) {
            $tagName = urldecode($this->getRequest()->getParam('tagName'));
            $tagModel = Mage::getModel('Mage_Tag_Model_Tag');
            $tagModel->loadByName($tagName);
            if ($tagModel->getId() && $tagModel->getStatus()==$tagModel->getApprovedStatus()) {
                Mage::register('tag_model', $tagModel);
                $this->loadLayout(false);
                $this->renderLayout();
                return;
            }
        }
        $this->_forward('nofeed', 'index', 'rss');
    }

    public function notifystockAction()
    {
        $this->getResponse()->setHeader('Content-type', 'text/xml; charset=UTF-8');
        $this->loadLayout(false);
        $this->renderLayout();
    }

    public function reviewAction()
    {
        $this->getResponse()->setHeader('Content-type', 'text/xml; charset=UTF-8');
        $this->loadLayout(false);
        $this->renderLayout();
    }

    public function categoryAction()
    {
        if ($this->checkFeedEnable('category')) {
            $this->loadLayout(false);
            $this->renderLayout();
        }
    }

    /**
     * Controller predispatch method to change area for some specific action.
     *
     * @return Mage_Rss_CatalogController
     */
    public function preDispatch()
    {
        if ($this->getRequest()->getActionName() == 'notifystock') {
            $this->_currentArea = 'adminhtml';
            Mage::helper('Mage_Rss_Helper_Data')->authAdmin('catalog/products');
        }
        if ($this->getRequest()->getActionName() == 'review') {
            $this->_currentArea = 'adminhtml';
            Mage::helper('Mage_Rss_Helper_Data')->authAdmin('catalog/reviews_ratings');
        }
        return parent::preDispatch();
    }
}
