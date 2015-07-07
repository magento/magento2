<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Model\Layout;

/**
 * Layout Update model class
 *
 * @method int getIsTemporary() getIsTemporary()
 * @method int getLayoutLinkId() getLayoutLinkId()
 * @method string getUpdatedAt() getUpdatedAt()
 * @method string getXml() getXml()
 * @method \Magento\Widget\Model\Layout\Update setIsTemporary() setIsTemporary(int $isTemporary)
 * @method \Magento\Widget\Model\Layout\Update setHandle() setHandle(string $handle)
 * @method \Magento\Widget\Model\Layout\Update setXml() setXml(string $xml)
 * @method \Magento\Widget\Model\Layout\Update setStoreId() setStoreId(int $storeId)
 * @method \Magento\Widget\Model\Layout\Update setThemeId() setThemeId(int $themeId)
 * @method \Magento\Widget\Model\Layout\Update setUpdatedAt() setUpdatedAt(string $updateDateTime)
 * @method \Magento\Widget\Model\Resource\Layout\Update\Collection getCollection()
 */
class Update extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $_dateTime;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_dateTime = $dateTime;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Layout Update model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Widget\Model\Resource\Layout\Update');
    }

    /**
     * Set current updated date
     *
     * @return \Magento\Framework\Model\AbstractModel
     */
    public function beforeSave()
    {
        $this->setUpdatedAt($this->_dateTime->formatDate(time()));
        return parent::beforeSave();
    }
}
