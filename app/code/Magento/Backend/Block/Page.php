<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

/**
 * Adminhtml page
 */
namespace Magento\Backend\Block;

/**
 * @api
 * @since 100.0.2
 */
class Page extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @param Template\Context $context
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_localeResolver = $localeResolver;
    }

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->pageConfig->addBodyClass($this->_request->getFullActionName('-'));
    }

    /**
     * Get current language
     *
     * @return string
     */
    public function getLang()
    {
        if (!$this->hasData('lang')) {
            $this->setData('lang', substr($this->_localeResolver->getLocale(), 0, 2));
        }
        return $this->getData('lang');
    }

    /**
     * Returns true if we are running in single store mode
     *
     * @return bool
     */
    public function isSingleStoreMode()
    {
        return $this->_storeManager->isSingleStoreMode();
    }
}
