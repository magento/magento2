<?php
/**
 * Backend locale switcher block
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Page;

class Locale extends \Magento\Backend\Block\Template
{
    /**
     * Path to template file in theme
     *
     * @var string
     */
    protected $_template = 'page/locale.phtml';

    /**
     * @var \Magento\Framework\Locale\ListsInterface
     */
    protected $_localeLists;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @var \Magento\Core\Helper\Url
     */
    protected $_urlHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Locale\ListsInterface $localeLists
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Core\Helper\Url $urlHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Locale\ListsInterface $localeLists,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Core\Helper\Url $urlHelper,
        array $data = []
    ) {
        $this->_localeLists = $localeLists;
        $this->_localeResolver = $localeResolver;
        $this->_urlHelper = $urlHelper;
        parent::__construct($context, $data);
    }

    /**
     * Prepare URL for change locale
     *
     * @return string
     */
    public function getChangeLocaleUrl()
    {
        return $this->getUrl('adminhtml/index/changeLocale');
    }

    /**
     * Prepare current URL for referer
     *
     * @return string
     */
    public function getUrlForReferer()
    {
        return \Magento\Framework\App\Action\Action::PARAM_NAME_URL_ENCODED . '/' . $this->_urlHelper->getEncodedUrl();
    }

    /**
     * Retrieve locale select element
     *
     * @return string
     */
    public function getLocaleSelect()
    {
        $html = $this->getLayout()->createBlock('Magento\Framework\View\Element\Html\Select')
            ->setName('locale')
            ->setId('footer_interface_locale')
            ->setTitle(__('Interface Language'))
            ->setClass('select locale-switcher-select')
            ->setValue($this->_localeResolver->getLocale()->__toString())
            ->setOptions($this->_localeLists->getTranslatedOptionLocales())
            ->getHtml();

        return $html;
    }
}
