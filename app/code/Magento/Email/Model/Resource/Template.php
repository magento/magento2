<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Email
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Template db resource
 *
 * @category    Magento
 * @package     Magento_Email
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Email\Model\Resource;

class Template extends \Magento\Core\Model\Resource\Db\AbstractDb
{
    /**
     * @var \Magento\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @param \Magento\Stdlib\DateTime $dateTime
     * @param \Magento\App\Resource $resource
     */
    public function __construct(\Magento\Stdlib\DateTime $dateTime, \Magento\App\Resource $resource)
    {
        $this->dateTime = $dateTime;
        parent::__construct($resource);
    }

    /**
     * Initialize email template resource model
     *
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
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable())
            ->where('template_code = :template_code');
        $result = $this->_getReadAdapter()->fetchRow($select, array('template_code' => $templateCode));

        if (!$result) {
            return array();
        }
        return $result;
    }

    /**
     * Check usage of template code in other templates
     *
     * @param \Magento\Email\Model\Template $template
     * @return boolean
     */
    public function checkCodeUsage(\Magento\Email\Model\Template $template)
    {
        if ($template->getTemplateActual() != 0 || is_null($template->getTemplateActual())) {
            $select = $this->_getReadAdapter()->select()
                ->from($this->getMainTable(), 'COUNT(*)')
                ->where('template_code = :template_code');
            $bind = array(
                'template_code' => $template->getTemplateCode()
            );

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
     * @param \Magento\Email\Model\Template $object
     * @return \Magento\Email\Model\Resource\Template
     */
    protected function _beforeSave(\Magento\Core\Model\AbstractModel $object)
    {
        if ($object->isObjectNew()) {
            $object->setCreatedAt($this->dateTime->formatDate(true));
        }
        $object->setModifiedAt($this->dateTime->formatDate(true));
        $object->setTemplateType((int)$object->getTemplateType());

        return parent::_beforeSave($object);
    }

    /**
     * Retrieve config scope and scope id of specified email template by email pathes
     *
     * @param array $paths
     * @param int|string $templateId
     * @return array
     */
    public function getSystemConfigByPathsAndTemplateId($paths, $templateId)
    {
        $orWhere = array();
        $pathesCounter = 1;
        $bind = array();
        foreach ($paths as $path) {
            $pathAlias = 'path_' . $pathesCounter;
            $orWhere[] = 'path = :' . $pathAlias;
            $bind[$pathAlias] = $path;
            $pathesCounter++;
        }
        $bind['template_id'] = $templateId;
        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('core_config_data'), array('scope', 'scope_id', 'path'))
            ->where('value LIKE :template_id')
            ->where(join(' OR ', $orWhere));

        return $this->_getReadAdapter()->fetchAll($select, $bind);
    }
}
