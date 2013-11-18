<?php
/**
 * Represents an endpoint to which messages can be sent
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
 * @category    Magento
 * @package     Magento_Outbound
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Outbound;

interface EndpointInterface
{
    /**
     * Data formats
     */
    const FORMAT_JSON = 'json';
    const FORMAT_XML = 'xml';

    /** Authentication types */
    const AUTH_TYPE_HMAC = 'hmac';
    const AUTH_TYPE_NONE = 'none';

    /**
     * Returns the endpoint URL of this subscription
     *
     * @return string
     */
    public function getEndpointUrl();

    /**
     * Returns the maximum time in seconds that this subscription is willing to wait before a retry should be attempted
     *
     * @return int
     */
    public function getTimeoutInSecs();

    /**
     * Returns the format this message should be sent in (JSON, XML, etc.)
     *
     * @return string
     */
    public function getFormat();


    /**
     * Returns the user abstraction associated with this subscription or null if no user has been associated yet.
     *
     * @return \Magento\Outbound\UserInterface|null
     */
    public function getUser();

    /**
     * Returns the type of authentication to use when attaching authentication to a message
     *
     * @return string
     */
    public function getAuthenticationType();

}
