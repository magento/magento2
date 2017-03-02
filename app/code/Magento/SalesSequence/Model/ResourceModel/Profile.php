<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesSequence\Model\ResourceModel;

use Magento\SalesSequence\Model\Meta as ModelMeta;
use Magento\Framework\Model\ResourceModel\Db\Context as DatabaseContext;
use Magento\SalesSequence\Model\ProfileFactory;

/**
 * Class Profile represents profile data for sequence as prefix, suffix, start value etc.
 */
class Profile extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'sales_sequence_profile';

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('sales_sequence_profile', 'profile_id');
    }

    /**
     * @var ProfileFactory
     */
    protected $profileFactory;

    /**
     * @param DatabaseContext $context
     * @param ProfileFactory $profileFactory
     * @param string $connectionName
     */
    public function __construct(
        DatabaseContext $context,
        ProfileFactory $profileFactory,
        $connectionName = null
    ) {
        $this->profileFactory = $profileFactory;
        parent::__construct($context, $connectionName);
    }

    /**
     * Load active profile
     *
     * @param int $metadataId
     * @return \Magento\SalesSequence\Model\Profile
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadActiveProfile($metadataId)
    {
        $profile = $this->profileFactory->create();
        $connection = $this->getConnection();
        $bind = ['meta_id' => $metadataId];
        $select = $connection->select()
            ->from($this->getMainTable(), ['profile_id'])
            ->where('meta_id = :meta_id')
            ->where('is_active = 1');

        $profileId = $connection->fetchOne($select, $bind);

        if ($profileId) {
            $this->load($profile, $profileId);
        }
        return $profile;
    }
}
