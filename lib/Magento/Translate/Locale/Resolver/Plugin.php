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
 * @package     Framework
 * @subpackage  Translate
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Magento translate abstract adapter
 */
namespace Magento\Translate\Locale\Resolver;

class Plugin
{
    /**
     * @var \Magento\TranslateInterface
     */
    protected $_translate;

    /**
     * @param \Magento\TranslateInterface $translate
     */
    function __construct(\Magento\TranslateInterface $translate)
    {
        $this->_translate = $translate;
    }

    /**
     * @param \Magento\Locale\ResolverInterface $subject
     * @param string|null $localeCode
     */
    public function afterEmulate(\Magento\Locale\ResolverInterface $subject, $localeCode)
    {
        $this->_init($localeCode);
    }

    /**
     * @param \Magento\Locale\ResolverInterface $subject
     * @param string|null $localeCode
     */
    public function afterRevert(\Magento\Locale\ResolverInterface $subject, $localeCode)
    {
        $this->_init($localeCode);
    }

    /**
     * @param string|null $localeCode
     */
    protected function _init($localeCode)
    {
        if (!is_null($localeCode)) {
            $this->_translate->initLocale($localeCode);
        }
    }
}
