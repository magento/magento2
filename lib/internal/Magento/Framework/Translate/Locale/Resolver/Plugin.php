<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Translate\Locale\Resolver;

/**
 * Magento translate abstract adapter
 */
class Plugin
{
    /**
     * @var \Magento\Framework\TranslateInterface
     */
    protected $_translate;

    /**
     * @param \Magento\Framework\TranslateInterface $translate
     */
    public function __construct(\Magento\Framework\TranslateInterface $translate)
    {
        $this->_translate = $translate;
    }

    /**
     * @param \Magento\Framework\Locale\ResolverInterface $subject
     * @param string|null $localeCode
     * @return void
     */
    public function afterEmulate(\Magento\Framework\Locale\ResolverInterface $subject, $localeCode)
    {
        $this->_init($localeCode);
    }

    /**
     * @param \Magento\Framework\Locale\ResolverInterface $subject
     * @param string|null $localeCode
     * @return void
     */
    public function afterRevert(\Magento\Framework\Locale\ResolverInterface $subject, $localeCode)
    {
        $this->_init($localeCode);
    }

    /**
     * @param string|null $localeCode
     * @return void
     */
    protected function _init($localeCode)
    {
        if (!is_null($localeCode)) {
            $this->_translate->setLocale($localeCode)
                ->loadData();
        }
    }
}
