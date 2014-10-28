<?php
namespace Magento\GoogleOptimizer\Model;

/**
 * Google Experiment Code Model
 *
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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @method \Magento\GoogleOptimizer\Model\Resource\Code _getResource()
 * @method \Magento\GoogleOptimizer\Model\Resource\Code getResource()
 * @method \Magento\GoogleOptimizer\Model\Code setEntityId(int $value)
 * @method string getEntityId()
 * @method \Magento\GoogleOptimizer\Model\Code setEntityType(string $value)
 * @method string getEntityType()
 * @method \Magento\GoogleOptimizer\Model\Code setStoreId(int $value)
 * @method int getStoreId()
 * @method \Magento\GoogleOptimizer\Model\Code setExperimentScript(int $value)
 * @method string getExperimentScript()
 */
class Code extends \Magento\Framework\Model\AbstractModel
{
    /**#@+
     * Entity types
     */
    const ENTITY_TYPE_PRODUCT = 'product';

    const ENTITY_TYPE_CATEGORY = 'category';

    const ENTITY_TYPE_PAGE = 'cms';

    /**#@-*/

    /**
     * @var bool
     */
    protected $_validateEntryFlag = false;

    /**
     * Model construct that should be used for object initialization
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Magento\GoogleOptimizer\Model\Resource\Code');
    }

    /**
     * Loading by entity id and type type
     *
     * @param int $entityId
     * @param string $entityType One of self::CODE_ENTITY_TYPE_
     * @param int $storeId
     * @return $this
     */
    public function loadByEntityIdAndType($entityId, $entityType, $storeId = 0)
    {
        $this->getResource()->loadByEntityType($this, $entityId, $entityType, $storeId);
        $this->_afterLoad();
        return $this;
    }
}
