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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Oauth;

/**
 * Interface NonceGeneratorInterface
 *
 * This interface provides methods for generating a nonce for a consumer and validating a nonce to ensure that it
 * is not already used by an existing consumer. Validation will persist the nonce if validation succeeds. A method
 * for generating a current timestamp is also provided by this interface.
 *
 */
interface NonceGeneratorInterface
{
    /**
     * Generate a new nonce for the consumer (if consumer is specified).
     *
     * @param ConsumerInterface $consumer
     * @return string - The generated nonce value.
     */
    public function generateNonce(ConsumerInterface $consumer = null);

    /**
     * Generate a current timestamp.
     *
     * @return int
     */
    public function generateTimestamp();

    /**
     * Validate the specified nonce, which ensures that it can only be used by a single consumer and persist it
     * with the specified consumer and timestamp. This method effectively saves the nonce and marks it as used
     * by the specified consumer.
     *
     * @param ConsumerInterface $consumer
     * @param string $nonce - The nonce value.
     * @param int $timestamp - The 'oauth_timestamp' value.
     * @return void
     * @throws \Magento\Framework\Oauth\Exception - Exceptions are thrown for validation errors.
     */
    public function validateNonce(ConsumerInterface $consumer, $nonce, $timestamp);
}
