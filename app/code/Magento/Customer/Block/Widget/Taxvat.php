<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Block\Widget;

use Magento\Customer\Api\CustomerMetadataInterface;

/**
 * Customer Value Added Tax Widget
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Taxvat extends AbstractWidget
{
    /**
     * Constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Helper\Address $addressHelper
     * @param CustomerMetadataInterface $customerMetadata
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Helper\Address $addressHelper,
        CustomerMetadataInterface $customerMetadata,
        array $data = []
    ) {
        parent::__construct($context, $addressHelper, $customerMetadata, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * Sets the template
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('Magento_Customer::widget/taxvat.phtml');
    }

    /**
     * Get is enabled.
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->_getAttribute('taxvat') ? (bool)$this->_getAttribute('taxvat')->isVisible() : false;
    }

    /**
     * Get is required.
     *
     * @return bool
     */
    public function isRequired()
    {
        return $this->_getAttribute('taxvat') ? (bool)$this->_getAttribute('taxvat')->isRequired() : false;
    }
}
