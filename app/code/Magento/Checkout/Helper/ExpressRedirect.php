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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Checkout\Helper;

use Magento\Checkout\Controller\Express\RedirectLoginInterface;

class ExpressRedirect extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\App\ActionFlag
     */
    protected $_actionFlag;

    /**
     * @var \Magento\Framework\ObjectManager
     */
    protected $_objectManager;

    /**
     * Customer session
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @param \Magento\Framework\App\ActionFlag $actionFlag
     * @param \Magento\Framework\ObjectManager $objectManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\ActionFlag $actionFlag,
        \Magento\Framework\ObjectManager $objectManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->_actionFlag = $actionFlag;
        $this->_objectManager = $objectManager;
        $this->_customerSession = $customerSession;

        parent::__construct($context);
    }

    /**
     * Performs redirect to login for checkout
     * @param RedirectLoginInterface $expressRedirect
     * @param string|null $customerBeforeAuthUrlDefault
     * @return void
     */
    public function redirectLogin(RedirectLoginInterface $expressRedirect, $customerBeforeAuthUrlDefault = null)
    {
        $this->_actionFlag->set('', 'no-dispatch', true);
        foreach ($expressRedirect->getActionFlagList() as $actionKey => $actionFlag) {
            $this->_actionFlag->set('', $actionKey, $actionFlag);
        }

        $expressRedirect->getResponse()->setRedirect(
            $this->_objectManager->get(
                'Magento\Core\Helper\Url'
            )->addRequestParam(
                $expressRedirect->getLoginUrl(),
                array('context' => 'checkout')
            )
        );

        $customerBeforeAuthUrl = $customerBeforeAuthUrlDefault;
        if ($expressRedirect->getCustomerBeforeAuthUrl()) {
            $customerBeforeAuthUrl = $expressRedirect->getCustomerBeforeAuthUrl();
        }
        if ($customerBeforeAuthUrl) {
            $this->_customerSession->setBeforeAuthUrl($customerBeforeAuthUrl);
        }
    }
}
