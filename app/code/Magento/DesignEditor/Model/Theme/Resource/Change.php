<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DesignEditor\Model\Theme\Resource;

/**
 * Theme change resource model
 */
class Change extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @param \Magento\Framework\Model\Resource\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param string|null $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\Resource\Db\Context $context,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        $resourcePrefix = null
    ) {
        $this->dateTime = $dateTime;
        parent::__construct($context, $resourcePrefix);
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('vde_theme_change', 'change_id');
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Framework\Model\AbstractModel $change
     * @return $this
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $change)
    {
        if (!$change->getChangeTime()) {
            $change->setChangeTime($this->dateTime->formatDate(true));
        }
        return $this;
    }
}
