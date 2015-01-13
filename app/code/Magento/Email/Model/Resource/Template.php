<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Model\Resource;

use Magento\Framework\Model\AbstractModel;

/**
 * Template db resource
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Template extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     */
    public function __construct(\Magento\Framework\App\Resource $resource, \Magento\Framework\Stdlib\DateTime $dateTime)
    {
        $this->dateTime = $dateTime;
        parent::__construct($resource);
    }

    /**
     * Initialize email template resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('email_template', 'template_id');
    }

    /**
     * Load by template code from DB.
     *
     * @param string $templateCode
     * @return array
     */
    public function loadByCode($templateCode)
    {
        $select = $this->_getReadAdapter()->select()->from(
            $this->getMainTable()
        )->where(
            'template_code = :template_code'
        );
        $result = $this->_getReadAdapter()->fetchRow($select, ['template_code' => $templateCode]);

        if (!$result) {
            return [];
        }
        return $result;
    }

    /**
     * Check usage of template code in other templates
     *
     * @param \Magento\Email\Model\Template $template
     * @return bool
     */
    public function checkCodeUsage(\Magento\Email\Model\Template $template)
    {
        if ($template->getTemplateActual() != 0 || is_null($template->getTemplateActual())) {
            $select = $this->_getReadAdapter()->select()->from(
                $this->getMainTable(),
                'COUNT(*)'
            )->where(
                'template_code = :template_code'
            );
            $bind = ['template_code' => $template->getTemplateCode()];

            $templateId = $template->getId();
            if ($templateId) {
                $select->where('template_id != :template_id');
                $bind['template_id'] = $templateId;
            }

            $result = $this->_getReadAdapter()->fetchOne($select, $bind);
            if ($result) {
                return true;
            }
        }
        return false;
    }

    /**
     * Set template type, added at and modified at time
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _beforeSave(AbstractModel $object)
    {
        if ($object->isObjectNew()) {
            $object->setAddedAt($this->dateTime->formatDate(true));
        }
        $object->setModifiedAt($this->dateTime->formatDate(true));
        $object->setTemplateType((int)$object->getTemplateType());

        return parent::_beforeSave($object);
    }

    /**
     * Retrieve config scope and scope id of specified email template by email paths
     *
     * @param array $paths
     * @param int|string $templateId
     * @return array
     */
    public function getSystemConfigByPathsAndTemplateId($paths, $templateId)
    {
        $orWhere = [];
        $pathsCounter = 1;
        $bind = [];
        foreach ($paths as $path) {
            $pathAlias = 'path_' . $pathsCounter;
            $orWhere[] = 'path = :' . $pathAlias;
            $bind[$pathAlias] = $path;
            $pathsCounter++;
        }
        $bind['template_id'] = $templateId;
        $select = $this->_getReadAdapter()->select()->from(
            $this->getTable('core_config_data'),
            ['scope', 'scope_id', 'path']
        )->where(
            'value LIKE :template_id'
        )->where(
            join(' OR ', $orWhere)
        );

        return $this->_getReadAdapter()->fetchAll($select, $bind);
    }
}
