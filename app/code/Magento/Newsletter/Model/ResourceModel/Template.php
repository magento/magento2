<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Newsletter\Model\ResourceModel;

/**
 * Newsletter template resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Template extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Date
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->_date = $date;
    }

    /**
     * Initialize connection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('newsletter_template', 'template_id');
    }

    /**
     * Check usage of template in queue
     *
     * @param \Magento\Newsletter\Model\Template $template
     * @return boolean
     */
    public function checkUsageInQueue(\Magento\Newsletter\Model\Template $template)
    {
        if ($template->getTemplateActual() !== 0 && !$template->getIsSystem()) {
            $select = $this->getConnection()->select()->from(
                $this->getTable('newsletter_queue'),
                new \Zend_Db_Expr('COUNT(queue_id)')
            )->where(
                'template_id = :template_id'
            );

            $countOfQueue = $this->getConnection()->fetchOne($select, ['template_id' => $template->getId()]);

            return $countOfQueue > 0;
        } elseif ($template->getIsSystem()) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Check usage of template code in other templates
     *
     * @param \Magento\Newsletter\Model\Template $template
     * @return boolean
     */
    public function checkCodeUsage(\Magento\Newsletter\Model\Template $template)
    {
        if ($template->getTemplateActual() != 0 || is_null($template->getTemplateActual())) {
            $bind = [
                'template_id' => $template->getId(),
                'template_code' => $template->getTemplateCode(),
                'template_actual' => 1,
            ];
            $select = $this->getConnection()->select()->from(
                $this->getMainTable(),
                new \Zend_Db_Expr('COUNT(template_id)')
            )->where(
                'template_id != :template_id'
            )->where(
                'template_code = :template_code'
            )->where(
                'template_actual = :template_actual'
            );

            $countOfCodes = $this->getConnection()->fetchOne($select, $bind);

            return $countOfCodes > 0;
        } else {
            return false;
        }
    }

    /**
     * Perform actions before object save
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($this->checkCodeUsage($object)) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Duplicate template code'));
        }

        if (!$object->hasTemplateActual()) {
            $object->setTemplateActual(1);
        }
        if (!$object->hasAddedAt()) {
            $object->setAddedAt($this->_date->gmtDate());
        }
        $object->setModifiedAt($this->_date->gmtDate());

        return parent::_beforeSave($object);
    }
}
