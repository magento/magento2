<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Block\Header;

/**
 * Remember Me block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Additional extends \Magento\Framework\View\Element\Html\Link
{
    /**
     * @var \Magento\Customer\Helper\View
     */
    protected $_customerViewHelper;

    /**
     * @var \Magento\Persistent\Helper\Session
     */
    protected $_persistentSessionHelper;

    /**
     * Customer repository
     *
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Helper\View $customerViewHelper
     * @param \Magento\Persistent\Helper\Session $persistentSessionHelper
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Helper\View $customerViewHelper,
        \Magento\Persistent\Helper\Session $persistentSessionHelper,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        array $data = []
    ) {
        $this->isScopePrivate = true;
        $this->_customerViewHelper = $customerViewHelper;
        $this->_persistentSessionHelper = $persistentSessionHelper;
        $this->customerRepository = $customerRepository;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * Retrieve unset cookie link
     *
     * @return string
     */
    public function getHref()
    {
        return $this->getUrl('persistent/index/unsetCookie');
    }

    /**
     * Render additional header html
     *
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->_persistentSessionHelper->getSession()->getCustomerId()) {
            $persistentName = $this->escapeHtml(
                $this->_customerViewHelper->getCustomerName(
                    $this->customerRepository->getById($this->_persistentSessionHelper->getSession()->getCustomerId())
                )
            );
            return '<span><a ' . $this->getLinkAttributes() . ' >' . __('(Not %1?)', $persistentName)
            . '</a></span>';
        }

        return '';
    }
}
