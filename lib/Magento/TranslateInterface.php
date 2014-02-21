<?php
/**
 * Translator interface
 *
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento;

/**
 * @todo change this interface when i18n-related logic is moved to library
 */
interface TranslateInterface
{
    /**
     * Default translation string
     */
    const DEFAULT_STRING = 'Translate String';

    /**
     * Determine if translation is enabled and allowed.
     *
     * @param mixed $scope
     * @return bool
     */
    public function isAllowed($scope = null);

    /**
     * Initialization translation data
     *
     * @param string $area
     * @param \Magento\Object $initParams
     * @param bool $forceReload
     * @return \Magento\TranslateInterface
     */
    public function init($area = null, $initParams = null, $forceReload = false);

    /**
     * Retrieve active translate mode
     *
     * @return bool
     */
    public function getTranslateInline();

    /**
     * Set Translate inline mode
     *
     * @param bool $flag
     * @return \Magento\TranslateInterface
     */
    public function setTranslateInline($flag);

    /**
     * Set locale
     *
     * @param $locale
     * @return \Magento\TranslateInterface
     */
    public function setLocale($locale);

    /**
     * Translate
     *
     * @param array $args
     * @return string
     */
    public function translate($args);
}
