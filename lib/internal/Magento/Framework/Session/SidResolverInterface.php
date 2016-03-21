<?php
/**
 * SID resolver interface
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
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
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getUseSessionInUrl();
}
