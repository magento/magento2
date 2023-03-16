<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Model;

use Magento\Framework\Data\Collection\AbstractDb as DbCollection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context as ModelContext;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Search\Model\ResourceModel\SynonymReader as ResourceSynonymReader;

/**
 * Data model to retrieve synonyms by passed in phrase
 *
 * @method SynonymReader setGroupId(int $group)
 * @method int getGroupId()
 * @method SynonymReader setStoreId(int $storeId)
 * @method int getStoreId()
 * @method SynonymReader setWebsiteId(int $websiteId)
 * @method int getWebsiteId()
 * @method SynonymReader setSynonyms(string $value)
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
     * @param ModelContext $context
     * @param Registry $registry
     * @param AbstractResource $resource
     * @param DbCollection $resourceCollection
     * @param array $data
     */
    public function __construct( //phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod
        ModelContext     $context,
        Registry         $registry,
        AbstractResource $resource = null,
        DbCollection     $resourceCollection = null,
        array            $data = []
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
        $this->_init(ResourceSynonymReader::class);
    }

    /**
     * Load synonyms by user query phrase in context of current store view
     *
     * @param string $phrase
     * @return $this
     * @throws LocalizedException
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
