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
 * @category    Mage
 * @package     Mage_XmlConnect
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Configuration data recourse model
 *
 * @category    Mage
 * @package     Mage_Xmlconnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Model_Resource_ConfigData extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Initialize configuration data
     *
     * @return null
     */
    protected function _construct()
    {
        $this->_init('xmlconnect_config_data', null);
    }

    /**
     * Save config value
     *
     * @param int $applicationId
     * @param string $category
     * @param string $path
     * @param string $value
     * @return Mage_XmlConnect_Model_Resource_ConfigData
     */
    public function saveConfig($applicationId, $category, $path, $value)
    {
        $newData = array(
            'application_id' => $applicationId,
            'category'  => $category,
            'path'      => $path,
            'value'     => $value
        );

        $this->_getWriteAdapter()->insertOnDuplicate($this->getMainTable(), $newData, array('value'));
        return $this;
    }

    /**
     * Delete config value
     *
     * @param int $applicationId
     * @param bool $category
     * @param bool $path
     * @param bool $pathLike
     * @return Mage_XmlConnect_Model_Resource_ConfigData
     */
    public function deleteConfig($applicationId, $category = false, $path = false, $pathLike = true)
    {
        try {
            $this->_getWriteAdapter()->beginTransaction();
            $writeAdapter = $this->_getWriteAdapter();
            $deleteWhere[] = $writeAdapter->quoteInto('application_id=?', $applicationId);
            if ($category) {
                $deleteWhere[] = $writeAdapter->quoteInto('category=?', $category);
            }
            if ($path) {
                $deleteWhere[] = $pathLike ? $writeAdapter->quoteInto('path like ?', $path . '/%')
                    : $writeAdapter->quoteInto('path=?', $path);
            }
            $writeAdapter->delete($this->getMainTable(), $deleteWhere);
            $this->_getWriteAdapter()->commit();
        } catch (Mage_Core_Exception $e) {
            $this->_getWriteAdapter()->rollBack();
            throw $e;
        } catch (Exception $e){
            $this->_getWriteAdapter()->rollBack();
            Mage::logException($e);
        }

        return $this;
    }
}
