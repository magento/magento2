<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Test\Fixture;

use Mtf\Fixture\InjectableFixture;

/**
 * Class Integration
 * Integration data fixture
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class Integration extends InjectableFixture
{
    /**
     * @var string
     */
    protected $repositoryClass = 'Magento\Integration\Test\Repository\Integration';

    /**
     * @var string
     */
    protected $handlerInterface = 'Magento\Integration\Test\Handler\Integration\IntegrationInterface';

    protected $defaultDataSet = [
        'name' => 'default_integration_%isolation%',
        'email' => 'test_%isolation%@example.com',
        'resource_access' => 'All',
    ];

    protected $integration_id = [
        'attribute_code' => 'integration_id',
        'backend_type' => 'int',
        'is_required' => '1',
        'default_value' => '',
        'input' => '',
    ];

    protected $name = [
        'attribute_code' => 'name',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
        'group' => 'integration_info',
    ];

    protected $email = [
        'attribute_code' => 'email',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
        'group' => 'integration_info',
    ];

    protected $endpoint = [
        'attribute_code' => 'endpoint',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
        'group' => 'integration_info',
    ];

    protected $status = [
        'attribute_code' => 'status',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $consumer_id = [
        'attribute_code' => 'consumer_id',
        'backend_type' => 'int',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $created_at = [
        'attribute_code' => 'created_at',
        'backend_type' => 'timestamp',
        'is_required' => '',
        'default_value' => 'CURRENT_TIMESTAMP',
        'input' => '',
    ];

    protected $updated_at = [
        'attribute_code' => 'updated_at',
        'backend_type' => 'timestamp',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $setup_type = [
        'attribute_code' => 'setup_type',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '0',
        'input' => '',
    ];

    protected $identity_link_url = [
        'attribute_code' => 'identity_link_url',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
        'group' => 'integration_info',
    ];

    protected $entity_id = [
        'attribute_code' => 'entity_id',
        'backend_type' => 'int',
        'is_required' => '1',
        'default_value' => '',
        'input' => '',
    ];

    protected $admin_id = [
        'attribute_code' => 'admin_id',
        'backend_type' => 'int',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $customer_id = [
        'attribute_code' => 'customer_id',
        'backend_type' => 'int',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $type = [
        'attribute_code' => 'type',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $token = [
        'attribute_code' => 'token',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $secret = [
        'attribute_code' => 'secret',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $verifier = [
        'attribute_code' => 'verifier',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $callback_url = [
        'attribute_code' => 'callback_url',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $revoked = [
        'attribute_code' => 'revoked',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '0',
        'input' => '',
    ];

    protected $authorized = [
        'attribute_code' => 'authorized',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '0',
        'input' => '',
    ];

    protected $user_type = [
        'attribute_code' => 'user_type',
        'backend_type' => 'int',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $key = [
        'attribute_code' => 'key',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $rejected_callback_url = [
        'attribute_code' => 'rejected_callback_url',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $resource_access = [
        'attribute_code' => 'resource_access',
        'backend_type' => 'virtual',
        'group' => 'api',
    ];

    protected $resources = [
        'attribute_code' => 'resources',
        'backend_type' => 'virtual',
        'group' => 'api',
    ];

    protected $token_secret = [
        'attribute_code' => 'token_secret',
        'backend_type' => 'virtual',
        'group' => 'integration_info',
    ];

    protected $consumer_secret = [
        'attribute_code' => 'consumer_secret',
        'backend_type' => 'virtual',
        'group' => 'integration_info',
    ];

    public function getIntegrationId()
    {
        return $this->getData('integration_id');
    }

    public function getName()
    {
        return $this->getData('name');
    }

    public function getEmail()
    {
        return $this->getData('email');
    }

    public function getEndpoint()
    {
        return $this->getData('endpoint');
    }

    public function getStatus()
    {
        return $this->getData('status');
    }

    public function getConsumerId()
    {
        return $this->getData('consumer_id');
    }

    public function getCreatedAt()
    {
        return $this->getData('created_at');
    }

    public function getUpdatedAt()
    {
        return $this->getData('updated_at');
    }

    public function getSetupType()
    {
        return $this->getData('setup_type');
    }

    public function getIdentityLinkUrl()
    {
        return $this->getData('identity_link_url');
    }

    public function getEntityId()
    {
        return $this->getData('entity_id');
    }

    public function getAdminId()
    {
        return $this->getData('admin_id');
    }

    public function getCustomerId()
    {
        return $this->getData('customer_id');
    }

    public function getType()
    {
        return $this->getData('type');
    }

    public function getToken()
    {
        return $this->getData('token');
    }

    public function getSecret()
    {
        return $this->getData('secret');
    }

    public function getVerifier()
    {
        return $this->getData('verifier');
    }

    public function getCallbackUrl()
    {
        return $this->getData('callback_url');
    }

    public function getRevoked()
    {
        return $this->getData('revoked');
    }

    public function getAuthorized()
    {
        return $this->getData('authorized');
    }

    public function getUserType()
    {
        return $this->getData('user_type');
    }

    public function getKey()
    {
        return $this->getData('key');
    }

    public function getRejectedCallbackUrl()
    {
        return $this->getData('rejected_callback_url');
    }

    public function getResourceAccess()
    {
        return $this->getData('resource_access');
    }

    public function getResources()
    {
        return $this->getData('resources');
    }

    public function getTokenSecret()
    {
        return $this->getData('token_secret');
    }

    public function getConsumerSecret()
    {
        return $this->getData('consumer_secret');
    }
}
