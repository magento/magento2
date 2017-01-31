<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Model;

/**
 * Integration model.
 *
 * @method \string getName()
 * @method Integration setName(\string $name)
 * @method \string getEmail()
 * @method Integration setEmail(\string $email)
 * @method Integration setStatus(\int $value)
 * @method \int getSetupType()
 * @method Integration setSetupType(\int $value)
 * @method Integration setConsumerId(\string $consumerId)
 * @method \string getConsumerId()
 * @method \string getEndpoint()
 * @method Integration setEndpoint(\string $endpoint)
 * @method \string getIdentityLinkUrl()
 * @method Integration setIdentityLinkUrl(\string $identityLinkUrl)
 * @method \string getCreatedAt()
 * @method Integration setCreatedAt(\string $createdAt)
 * @method \string getUpdatedAt()
 * @method Integration setUpdatedAt(\string $createdAt)
 * @method \Magento\Integration\Model\ResourceModel\Integration getResource()
 */
class Integration extends \Magento\Framework\Model\AbstractModel
{
    /**#@+
     * Integration Status values
     */
    const STATUS_INACTIVE = 0;

    const STATUS_ACTIVE = 1;

    const STATUS_RECREATED = 2;

    /**#@-*/

    /**#@+
     * Integration setup type
     */
    const TYPE_MANUAL = 0;

    const TYPE_CONFIG = 1;

    /**#@-*/

    /**#@+
     * Integration data key constants.
     */
    const ID = 'integration_id';

    const NAME = 'name';

    const EMAIL = 'email';

    const ENDPOINT = 'endpoint';

    const IDENTITY_LINK_URL = 'identity_link_url';

    const SETUP_TYPE = 'setup_type';

    const CONSUMER_ID = 'consumer_id';

    const STATUS = 'status';

    /**#@-*/

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Magento\Integration\Model\ResourceModel\Integration');
    }

    /**
     * Load integration by oAuth consumer ID.
     *
     * @param int $consumerId
     * @return $this
     */
    public function loadByConsumerId($consumerId)
    {
        return $this->load($consumerId, self::CONSUMER_ID);
    }

    /**
     * Load active integration by oAuth consumer ID.
     *
     * @param int $consumerId
     * @return $this
     */
    public function loadActiveIntegrationByConsumerId($consumerId)
    {
        $integrationData = $this->getResource()->selectActiveIntegrationByConsumerId($consumerId);
        $this->setData($integrationData ? $integrationData : []);
        return $this;
    }

    /**
     * Get integration status. Cast to the type of STATUS_* constants in order to make strict comparison valid.
     *
     * @return int
     * @api
     */
    public function getStatus()
    {
        return (int)$this->getData(self::STATUS);
    }
}
