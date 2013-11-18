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
 */
namespace Magento\CatalogSearch\Controller;

class Result extends \Magento\Core\Controller\Front\Action
{
    /**
     * Store manager
     *
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Catalog session
     *
     * @var \Magento\Catalog\Model\Session
     */
    protected $_catalogSession;

    /**
     * Construct
     *
     * @param \Magento\Core\Controller\Varien\Action\Context $context
     * @param \Magento\Catalog\Model\Session $catalogSession
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Core\Controller\Varien\Action\Context $context,
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Core\Model\StoreManagerInterface $storeManager
    ) {
        $this->_catalogSession = $catalogSession;
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * Retrieve catalog session
     *
     * @return \Magento\Catalog\Model\Session
     */
    protected function _getSession()
    {
        return $this->_catalogSession;
    }
    /**
     * Display search result
     */
    public function indexAction()
    {
        $query = $this->_objectManager->get('Magento\CatalogSearch\Helper\Data')->getQuery();
        /* @var $query \Magento\CatalogSearch\Model\Query */

        $query->setStoreId($this->_storeManager->getStore()->getId());

        if ($query->getQueryText() != '') {
            if ($this->_objectManager->get('Magento\CatalogSearch\Helper\Data')->isMinQueryLength()) {
                $query->setId(0)
                    ->setIsActive(1)
                    ->setIsProcessed(1);
            }
            else {
                if ($query->getId()) {
                    $query->setPopularity($query->getPopularity()+1);
                }
                else {
                    $query->setPopularity(1);
                }

                if ($query->getRedirect()){
                    $query->save();
                    $this->getResponse()->setRedirect($query->getRedirect());
                    return;
                }
                else {
                    $query->prepare();
                }
            }

            $this->_objectManager->get('Magento\CatalogSearch\Helper\Data')->checkNotes();

            $this->loadLayout();
            $this->_initLayoutMessages('Magento\Catalog\Model\Session');
            $this->_initLayoutMessages('Magento\Checkout\Model\Session');
            $this->renderLayout();

            if (!$this->_objectManager->get('Magento\CatalogSearch\Helper\Data')->isMinQueryLength()) {
                $query->save();
            }
        }
        else {
            $this->_redirectReferer();
        }
    }
}
