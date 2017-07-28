<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Api;

use Magento\Integration\Model\Oauth\Token as OauthTokenModel;

/**
 * Integration oAuth Service Interface
 *
 * @api
 * @since 2.0.0
 */
interface OauthServiceInterface
{
    /**
     * Create a new consumer account.
     *
     * @param array $consumerData - Information provided by an integration when the integration is installed.
     * <pre>
     * array(
     *     'name' => 'Integration Name',
     *     '...' => '...', // Other consumer data can be passed as well
     * )
     * </pre>
     * @return \Magento\Integration\Model\Oauth\Consumer
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Oauth\Exception
     * @since 2.0.0
     */
    public function createConsumer($consumerData);

    /**
     * Create access token for provided consumer.
     *
     * @param int $consumerId
     * @param bool $clearExistingToken
     * @return bool If token was created
     * @since 2.0.0
     */
    public function createAccessToken($consumerId, $clearExistingToken = false);

    /**
     * Retrieve access token assigned to the consumer.
     *
     * @param int $consumerId
     * @return OauthTokenModel|bool Return false if no access token is available.
     * @since 2.0.0
     */
    public function getAccessToken($consumerId);

    /**
     * Load consumer by its ID.
     *
     * @param int $consumerId
     * @return \Magento\Integration\Model\Oauth\Consumer
     * @throws \Magento\Framework\Oauth\Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function loadConsumer($consumerId);

    /**
     * Load consumer by its key.
     *
     * @param string $key
     * @return \Magento\Integration\Model\Oauth\Consumer
     * @throws \Magento\Framework\Oauth\Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function loadConsumerByKey($key);

    /**
     * Execute post to integration (consumer) HTTP Post URL. Generate and return oauth_verifier.
     *
     * @param int $consumerId - The consumer Id.
     * @param string $endpointUrl - The integration endpoint Url (for HTTP Post)
     * @return string - The oauth_verifier.
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Oauth\Exception
     * @since 2.0.0
     */
    public function postToConsumer($consumerId, $endpointUrl);

    /**
     * Delete the consumer data associated with the integration including its token and nonce
     *
     * @param int $consumerId
     * @return array Consumer data array
     * @since 2.0.0
     */
    public function deleteConsumer($consumerId);

    /**
     * Remove token associated with provided consumer.
     *
     * @param int $consumerId
     * @return bool If token was deleted
     * @since 2.0.0
     */
    public function deleteIntegrationToken($consumerId);
}
