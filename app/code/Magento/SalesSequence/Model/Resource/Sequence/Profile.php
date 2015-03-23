<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\SalesSequence\Model\Resource\Sequence;

use Magento\SalesSequence\Model\Sequence\Meta as ModelMeta;
use Magento\Framework\Model\Resource\Db\Context as DatabaseContext;
use Magento\SalesSequence\Model\Sequence\ProfileFactory;

/**
 * Class Profile
 */
class Profile extends \Magento\Framework\Model\Resource\Db\AbstractDb
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
     * @param null $resourcePrefix
     */
    public function __construct(
        DatabaseContext $context,
        ProfileFactory $profileFactory,
        $resourcePrefix = null
    ) {
        $this->profileFactory = $profileFactory;
        parent::__construct($context, $resourcePrefix);
    }

    /**
     * Load active profile
     *
     * @param $metadataId
     * @return \Magento\SalesSequence\Model\Sequence\Profile
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadActiveProfile($metadataId)
    {
        $profile = $this->profileFactory->create();
        $adapter = $this->_getReadAdapter();
        $bind = ['meta_id' => $metadataId];
        $select = $adapter->select()
            ->from($this->getMainTable(), ['profile_id'])
            ->where('meta_id = :meta_id')
            ->where('is_active = 1');

        $profileId = $adapter->fetchOne($select, $bind);

        if ($profileId) {
            $this->load($profile, $profileId);
        }
        return $profile;
    }
}
