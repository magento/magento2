<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Model\Resource;

use Magento\Framework\Model\Resource\Db\AbstractDb;

/**
 * Bookmark resource
 */
class Bookmark extends AbstractDb
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @param \Magento\Framework\Model\Resource\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param null $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\Resource\Db\Context $context,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        $resourcePrefix = null
    ) {
        parent::__construct($context, $resourcePrefix);
        $this->dateTime = $dateTime;
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ui_bookmark', 'bookmark_id');
    }

    /**
     * Prepare data to be saved to database
     * @param \Magento\Framework\Model\AbstractModel|\Magento\Framework\Object $object
     *
     * @return $this
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->isObjectNew()) {
            $object->setCreatedAt($this->dateTime->formatDate(true));
        }
        $object->setUpdatedAt($this->dateTime->formatDate(true));
        return $this;
    }
}
