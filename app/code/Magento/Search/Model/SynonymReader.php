<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Model;

use Magento\Framework\Data\Collection\AbstractDb as DbCollection;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * Data model to retrieve synonyms by passed in phrase
 *
 * @method \Magento\Search\Model\SynonymReader setGroupId(int $group)
 * @method int getGroupId()
 * @method \Magento\Search\Model\SynonymReader setStoreId(int $storeId)
 * @method int getStoreId()
 * @method \Magento\Search\Model\SynonymReader setWebsiteId(int $websiteId)
 * @method int getWebsiteId()
 * @method \Magento\Search\Model\SynonymReader setSynonyms(string $value)
 * @method string getSynonyms()
 * @api
 * @since 100.1.0
 */
class SynonymReader extends AbstractModel
{
    /**
     * @var string
     * @since 100.1.0
     */
    protected $_eventPrefix = 'search_synonyms';

    /**
     * Event object key name
     *
     * @var string
     * @since 100.1.0
     */
    protected $_eventObject = 'search_synonyms';

    /**
     * Construct
     *
     * @param \Magento\Framework\Model\Context $context
     * @param Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param DbCollection $resourceCollection
     * @param array $data
     */
    public function __construct( //phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod
        \Magento\Framework\Model\Context $context,
        Registry $registry,
        ?AbstractResource $resource = null,
        ?DbCollection $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Init resource model
     *
     * @return void
     * @since 100.1.0
     */
    protected function _construct()
    {
        $this->_init(\Magento\Search\Model\ResourceModel\SynonymReader::class);
    }

    /**
     * Load synonyms by user query phrase in context of current store view
     *
     * @param string $phrase
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 100.1.0
     */
    public function loadByPhrase($phrase)
    {
        $this->_getResource()->loadByPhrase($this, $phrase !== null ? strtolower($phrase) : '');
        $this->_afterLoad();
        $this->setOrigData();
        return $this;
    }
}
