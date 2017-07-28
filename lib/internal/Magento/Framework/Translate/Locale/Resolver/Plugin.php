<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Translate\Locale\Resolver;

/**
 * Magento translate abstract adapter
 * @since 2.0.0
 */
class Plugin
{
    /**
     * @var \Magento\Framework\TranslateInterface
     * @since 2.0.0
     */
    protected $_translate;

    /**
     * @param \Magento\Framework\TranslateInterface $translate
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\TranslateInterface $translate)
    {
        $this->_translate = $translate;
    }

    /**
     * @param \Magento\Framework\Locale\ResolverInterface $subject
     * @param string|null $localeCode
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function afterEmulate(\Magento\Framework\Locale\ResolverInterface $subject, $localeCode)
    {
        $this->_init($localeCode);
    }

    /**
     * @param \Magento\Framework\Locale\ResolverInterface $subject
     * @param string|null $localeCode
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function afterRevert(\Magento\Framework\Locale\ResolverInterface $subject, $localeCode)
    {
        $this->_init($localeCode);
    }

    /**
     * @param string|null $localeCode
     * @return void
     * @since 2.0.0
     */
    protected function _init($localeCode)
    {
        if ($localeCode !== null) {
            $this->_translate->setLocale($localeCode)
                ->loadData();
        }
    }
}
