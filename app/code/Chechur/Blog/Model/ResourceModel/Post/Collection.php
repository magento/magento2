<?php
declare(strict_types=1);

namespace Chechur\Blog\Model\ResourceModel\Post;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'post_id';

    /**
     * @var string
     */
    protected $_eventPrefix = 'chechur_blog_post_collection';

    /**
     * @var string
     */
    protected $_eventObject = 'post_collection';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var int
     */
    protected $_storeId;

    protected $_helperData;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param Magento\Store\Model\StoreManagerInterface $storeManager
     * @param null|\Zend_Db_Adapter_Abstract $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     * @param \Chechur\Blog\Helper\Data $helperData
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Chechur\Blog\Helper\Data $helperData,
        $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    )
    {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->_date = $date;
        $this->_storeManager = $storeManager;
        $this->_helperData = $helperData;
    }


    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Chechur\Blog\Model\Post', 'Chechur\Blog\Model\ResourceModel\Post');
    }

    public function getTypeOfVisible()
    {
        $str = $this->_helperData->getGeneralConfig('multiselect');

        $strdata = explode(',', $str);

        return $strdata;

    }

}
