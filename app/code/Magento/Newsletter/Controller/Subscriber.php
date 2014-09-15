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

/**
 * Newsletter subscribe controller
 */
namespace Magento\Newsletter\Controller;

use Magento\Framework\App\Action\Context;
use Magento\Framework\StoreManagerInterface;
use Magento\Customer\Model\Session;
use Magento\Customer\Service\V1\CustomerAccountServiceInterface;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Customer\Helper\Data as CustomerHelper;

class Subscriber extends \Magento\Framework\App\Action\Action
{
    /**
     * Customer session
     *
     * @var Session
     */
    protected $_customerSession;

    /**
     * Customer Service
     *
     * @var CustomerAccountServiceInterface
     */
    protected $_customerService;

    /**
     * Subscriber factory
     *
     * @var SubscriberFactory
     */
    protected $_subscriberFactory;

    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var CustomerHelper
     */
    protected $_customerHelper;

    /**
     * @param Context $context
     * @param SubscriberFactory $subscriberFactory
     * @param CustomerAccountServiceInterface $customerService
     * @param Session $customerSession
     * @param StoreManagerInterface $storeManager
     * @param CustomerHelper $customerHelper
     */
    public function __construct(
        Context $context,
        SubscriberFactory $subscriberFactory,
        CustomerAccountServiceInterface $customerService,
        Session $customerSession,
        StoreManagerInterface $storeManager,
        CustomerHelper $customerHelper
    ) {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->_subscriberFactory = $subscriberFactory;
        $this->_customerService = $customerService;
        $this->_customerSession = $customerSession;
        $this->_customerHelper = $customerHelper;
    }
}
