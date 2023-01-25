<?php
/**
 * SID resolver interface
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Session;

/**
 * Interface \Magento\Framework\Session\SidResolverInterface
 *
 * @deprecated 2.3.3 SIDs in URLs are no longer used
 */
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
     * @return string|null
     * @deprecated SID query parameter is not used in URLs anymore.
     */
    public function getSid(\Magento\Framework\Session\SessionManagerInterface $session);

    /**
     * Get session id query param
     *
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @return string
     * @deprecated SID query parameter is not used in URLs anymore.
     */
    public function getSessionIdQueryParam(\Magento\Framework\Session\SessionManagerInterface $session);

    /**
     * Set use session var instead of SID for URL
     *
     * @param bool $var
     * @return $this
     * @deprecated SID query parameter is not used in URLs anymore.
     */
    public function setUseSessionVar($var);

    /**
     * Retrieve use flag session var instead of SID for URL
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getUseSessionVar();

    /**
     * Set Use session in URL flag
     *
     * @param bool $flag
     * @return $this
     * @deprecated SID query parameter is not used in URLs anymore.
     */
    public function setUseSessionInUrl($flag = true);

    /**
     * Retrieve use session in URL flag
     *
     * @return bool
     * @deprecated SID query parameter is not used in URLs anymore.
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getUseSessionInUrl();
}
