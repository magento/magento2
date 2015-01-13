<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Model;

/**
 * Persistent Observer
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Observer
{
    /**
     * Persistent session
     *
     * @var \Magento\Persistent\Helper\Session
     */
    protected $_persistentSession;

    /**
     * Layout model
     *
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $_layout;

    /**
     * Url model
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;

    /**
     * Customer view helper
     *
     * @var \Magento\Customer\Helper\View
     */
    protected $_customerViewHelper;

    /**
     * Customer repository
     *
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * Constructor
     *
     * @param \Magento\Persistent\Helper\Session $persistentSession
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Customer\Helper\View $customerViewHelper
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        \Magento\Persistent\Helper\Session $persistentSession,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Framework\Escaper $escaper,
        \Magento\Customer\Helper\View $customerViewHelper,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
    ) {
        $this->_persistentSession = $persistentSession;
        $this->_url = $url;
        $this->_layout = $layout;
        $this->_escaper = $escaper;
        $this->_customerViewHelper = $customerViewHelper;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Emulate 'welcome' block with persistent data
     *
     * @param \Magento\Framework\View\Element\AbstractBlock $block
     * @return $this
     */
    public function emulateWelcomeBlock($block)
    {
        $escapedName = $this->_escaper->escapeHtml(
            $this->_customerViewHelper->getCustomerName(
                $this->customerRepository->getById($this->_persistentSession->getSession()->getCustomerId())
            ),
            null
        );

        $this->_applyAccountLinksPersistentData();
        $welcomeMessage = __('Welcome, %1!', $escapedName)
            . ' ' . $this->_layout->getBlock('header.additional')->toHtml();
        $block->setWelcome($welcomeMessage);
        return $this;
    }

    /**
     * Emulate 'account links' block with persistent data
     *
     * @return void
     */
    protected function _applyAccountLinksPersistentData()
    {
        if (!$this->_layout->getBlock('header.additional')) {
            $this->_layout->addBlock('Magento\Persistent\Block\Header\Additional', 'header.additional');
        }
    }

    /**
     * Emulate 'top links' block with persistent data
     *
     * @param \Magento\Framework\View\Element\AbstractBlock $block
     * @return void
     */
    public function emulateTopLinks($block)
    {
        $this->_applyAccountLinksPersistentData();
        $block->removeLinkByUrl($this->_url->getUrl('customer/account/login'));
    }
}
