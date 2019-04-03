<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Model\ResourceModel;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Ui\Api\Data\BookmarkInterface;

/**
 * Bookmark resource
 */
class Bookmark extends AbstractDb
{
    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @param Context $context
     * @param string $connectionName
     * @param DateTime|null $dateTime
     */
    public function __construct(
        Context $context,
        $connectionName = null,
        DateTime $dateTime = null
    ) {
        parent::__construct($context, $connectionName);
        $this->dateTime = $dateTime ?: ObjectManager::getInstance()->get(DateTime::class);
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
     * Perform actions before object save
     *
     * @param AbstractModel|BookmarkInterface $object
     * @return $this
     */
    protected function _beforeSave(AbstractModel $object)
    {
        $gmtDate = $this->dateTime->gmtDate();

        $object->setUpdatedAt($gmtDate);
        if ($object->isObjectNew()) {
            $object->setCreatedAt($gmtDate);
        }

        return parent::_beforeSave($object);
    }
}
