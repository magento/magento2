<?php
/**
 * SID resolver interface
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
