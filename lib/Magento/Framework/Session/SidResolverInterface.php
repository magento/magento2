<?php
/**
 * SID resolver interface
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
namespace Magento\Framework\Session;

interface SidResolverInterface
{
    /**
     * Session ID in query param
     */
    const SESSION_ID_QUERY_PARAM = 'SID';

    /**
     * Get SID
     *
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @return string
     */
    public function getSid(\Magento\Framework\Session\SessionManagerInterface $session);

    /**
     * Get session id query param
     *
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @return string
     */
    public function getSessionIdQueryParam(\Magento\Framework\Session\SessionManagerInterface $session);

    /**
     * Set use session var instead of SID for URL
     *
     * @param bool $var
     * @return $this
     */
    public function setUseSessionVar($var);

    /**
     * Retrieve use flag session var instead of SID for URL
     *
     * @return bool
     */
    public function getUseSessionVar();

    /**
     * Set Use session in URL flag
     *
     * @param bool $flag
     * @return $this
     */
    public function setUseSessionInUrl($flag = true);

    /**
     * Retrieve use session in URL flag
     *
     * @return bool
     */
    public function getUseSessionInUrl();
}
