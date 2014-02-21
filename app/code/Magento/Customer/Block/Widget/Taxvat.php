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
 * @package     Magento_Customer
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Customer\Block\Widget;

class Taxvat extends \Magento\Customer\Block\Widget\AbstractWidget
{
    /**
     * @var \Magento\Customer\Model\Resource\Customer
     */
    protected $_customerResource;

    /**
     * @param \Magento\View\Element\Template\Context $context
     * @param \Magento\Customer\Helper\Address $addressHelper
     * @param \Magento\Customer\Service\V1\CustomerMetadataServiceInterface $attributeMetadata
     * @param \Magento\Customer\Model\Session $customerSession
     * @param array $data
     */
    public function __construct(
        \Magento\View\Element\Template\Context $context,
        \Magento\Customer\Helper\Address $addressHelper,
        \Magento\Customer\Service\V1\CustomerMetadataServiceInterface $attributeMetadata,
        \Magento\Customer\Model\Session $customerSession,
        array $data = array()
    ) {
        $this->_customerSession = $customerSession;
        parent::__construct($context, $addressHelper, $attributeMetadata, $data);
        $this->_isScopePrivate = true;
    }

    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('widget/taxvat.phtml');
    }

    public function isEnabled()
    {
        return $this->_getAttribute('taxvat') ? (bool)$this->_getAttribute('taxvat')->isVisible() : false;
    }

    public function isRequired()
    {
        return $this->_getAttribute('taxvat') ? (bool)$this->_getAttribute('taxvat')->isRequired() : false;
    }

    public function getCustomer()
    {
        return $this->_customerSession->getCustomer();
    }
}
