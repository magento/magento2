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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Integration\Service\V1;

use Magento\Integration\Model\Oauth\Token as OauthTokenModel;

/**
 * Integration oAuth Service Interface
 */
interface OauthInterface
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
     * @throws \Magento\Framework\Model\Exception
     * @throws \Magento\Framework\Oauth\Exception
     */
    public function createConsumer($consumerData);

    /**
     * Create access token for provided consumer.
     *
     * @param int $consumerId
     * @param bool $clearExistingToken
     * @return bool If token was created
     */
    public function createAccessToken($consumerId, $clearExistingToken = false);

    /**
     * Retrieve access token assigned to the consumer.
     *
     * @param int $consumerId
     * @return OauthTokenModel|bool Return false if no access token is available.
     */
    public function getAccessToken($consumerId);

    /**
     * Load consumer by its ID.
     *
     * @param int $consumerId
     * @return \Magento\Integration\Model\Oauth\Consumer
     * @throws \Magento\Framework\Oauth\Exception
     * @throws \Magento\Framework\Model\Exception
     */
    public function loadConsumer($consumerId);

    /**
     * Load consumer by its key.
     *
     * @param string $key
     * @return \Magento\Integration\Model\Oauth\Consumer
     * @throws \Magento\Framework\Oauth\Exception
     * @throws \Magento\Framework\Model\Exception
     */
    public function loadConsumerByKey($key);

    /**
     * Execute post to integration (consumer) HTTP Post URL. Generate and return oauth_verifier.
     *
     * @param int $consumerId - The consumer Id.
     * @param string $endpointUrl - The integration endpoint Url (for HTTP Post)
     * @return string - The oauth_verifier.
     * @throws \Magento\Framework\Model\Exception
     * @throws \Magento\Framework\Oauth\Exception
     */
    public function postToConsumer($consumerId, $endpointUrl);

    /**
     * Delete the consumer data associated with the integration including its token and nonce
     *
     * @param int $consumerId
     * @return array Consumer data array
     */
    public function deleteConsumer($consumerId);

    /**
     * Remove token associated with provided consumer.
     *
     * @param int $consumerId
     * @return bool If token was deleted
     */
    public function deleteIntegrationToken($consumerId);
}
