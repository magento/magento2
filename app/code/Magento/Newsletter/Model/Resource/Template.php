<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Model\Resource;

/**
 * Newsletter template resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Template extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Date
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     */
    public function __construct(\Magento\Framework\App\Resource $resource, \Magento\Framework\Stdlib\DateTime\DateTime $date)
    {
        parent::__construct($resource);
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
     * Load an object by template code
     *
     * @param \Magento\Newsletter\Model\Template $object
     * @param string $templateCode
     * @return $this
     */
    public function loadByCode(\Magento\Newsletter\Model\Template $object, $templateCode)
    {
        $read = $this->_getReadAdapter();
        if ($read && !is_null($templateCode)) {
            $select = $this->_getLoadSelect(
                'template_code',
                $templateCode,
                $object
            )->where(
                'template_actual = :template_actual'
            );
            $data = $read->fetchRow($select, ['template_actual' => 1]);

            if ($data) {
                $object->setData($data);
            }
        }

        $this->_afterLoad($object);

        return $this;
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
            $select = $this->_getReadAdapter()->select()->from(
                $this->getTable('newsletter_queue'),
                new \Zend_Db_Expr('COUNT(queue_id)')
            )->where(
                'template_id = :template_id'
            );

            $countOfQueue = $this->_getReadAdapter()->fetchOne($select, ['template_id' => $template->getId()]);

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
            $select = $this->_getReadAdapter()->select()->from(
                $this->getMainTable(),
                new \Zend_Db_Expr('COUNT(template_id)')
            )->where(
                'template_id != :template_id'
            )->where(
                'template_code = :template_code'
            )->where(
                'template_actual = :template_actual'
            );

            $countOfCodes = $this->_getReadAdapter()->fetchOne($select, $bind);

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
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($this->checkCodeUsage($object)) {
            throw new \Magento\Framework\Model\Exception(__('Duplicate template code'));
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
