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
namespace Magento\Customer\Block\Widget;

use Magento\Customer\Service\V1\CustomerMetadataServiceInterface;
use Magento\Customer\Service\V1\CustomerAccountServiceInterface;
use Magento\Customer\Service\V1\Data\Customer;
use Magento\Customer\Service\V1\Data\Eav\Option;

/**
 * Block to render customer's gender attribute
 */
class Gender extends AbstractWidget
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var CustomerAccountServiceInterface
     */
    protected $_customerAccountService;

    /**
     * Create an instance of the Gender widget
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Helper\Address $addressHelper
     * @param CustomerMetadataServiceInterface $customerMetadataService
     * @param CustomerAccountServiceInterface $customerAccountService
     * @param \Magento\Customer\Model\Session $customerSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Helper\Address $addressHelper,
        CustomerMetadataServiceInterface $customerMetadataService,
        CustomerAccountServiceInterface $customerAccountService,
        \Magento\Customer\Model\Session $customerSession,
        array $data = array()
    ) {
        $this->_customerSession = $customerSession;
        $this->_customerAccountService = $customerAccountService;
        parent::__construct($context, $addressHelper, $customerMetadataService, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * Initialize block
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('widget/gender.phtml');
    }

    /**
     * Check if gender attribute enabled in system
     * @return bool
     */
    public function isEnabled()
    {
        return $this->_getAttribute('gender') ? (bool)$this->_getAttribute('gender')->isVisible() : false;
    }

    /**
     * Check if gender attribute marked as required
     * @return bool
     */
    public function isRequired()
    {
        return $this->_getAttribute('gender') ? (bool)$this->_getAttribute('gender')->isRequired() : false;
    }

    /**
     * Get current customer from session using the customer service
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->_customerAccountService->getCustomer($this->_customerSession->getCustomerId());
    }

    /**
     * Returns options from gender attribute
     * @return Option[]
     */
    public function getGenderOptions()
    {
        return $this->_getAttribute('gender')->getOptions();
    }
}
