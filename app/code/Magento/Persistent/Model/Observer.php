<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Model;

/**
 * Persistent Observer
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 * @since 2.0.0
 */
class Observer
{
    /**
     * Persistent session
     *
     * @var \Magento\Persistent\Helper\Session
     * @since 2.0.0
     */
    protected $_persistentSession;

    /**
     * Layout model
     *
     * @var \Magento\Framework\View\LayoutInterface
     * @since 2.0.0
     */
    protected $_layout;

    /**
     * Url model
     *
     * @var \Magento\Framework\UrlInterface
     * @since 2.0.0
     */
    protected $_url;

    /**
     * @var \Magento\Framework\Escaper
     * @since 2.0.0
     */
    protected $_escaper;

    /**
     * Customer view helper
     *
     * @var \Magento\Customer\Helper\View
     * @since 2.0.0
     */
    protected $_customerViewHelper;

    /**
     * Customer repository
     *
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function emulateWelcomeBlock($block)
    {
        $customerName = $this->_customerViewHelper->getCustomerName(
                $this->customerRepository->getById($this->_persistentSession->getSession()->getCustomerId())
        );

        $this->_applyAccountLinksPersistentData();
        $welcomeMessage = __('Welcome, %1!', $customerName);
        $block->setWelcome($welcomeMessage);
        return $this;
    }

    /**
     * Emulate 'account links' block with persistent data
     *
     * @return void
     * @since 2.0.0
     */
    protected function _applyAccountLinksPersistentData()
    {
        if (!$this->_layout->getBlock('header.additional')) {
            $this->_layout->addBlock(\Magento\Persistent\Block\Header\Additional::class, 'header.additional');
        }
    }

    /**
     * Emulate 'top links' block with persistent data
     *
     * @param \Magento\Framework\View\Element\AbstractBlock $block
     * @return void
     * @since 2.0.0
     */
    public function emulateTopLinks($block)
    {
        $this->_applyAccountLinksPersistentData();
        /** @var \Magento\Framework\View\Element\Html\Link[] $links */
        $links = $block->getLinks();
        $removeLink = $this->_url->getUrl('customer/account/login');
        foreach ($links as $link) {
            if ($link->getHref() == $removeLink) {
                $this->_layout->unsetChild($block->getNameInLayout(), $link->getNameInLayout());
            }
        }
    }
}
