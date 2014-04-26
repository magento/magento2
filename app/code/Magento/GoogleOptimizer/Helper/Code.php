<?php
/**
 * Google Optimizer Scripts Helper
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
 */
namespace Magento\GoogleOptimizer\Helper;

class Code
{
    /**
     * @var \Magento\GoogleOptimizer\Model\Code
     */
    protected $_codeModel;

    /**
     * @var \Magento\Framework\Model\AbstractModel
     */
    protected $_entity;

    /**
     * @param \Magento\GoogleOptimizer\Model\Code $code
     */
    public function __construct(\Magento\GoogleOptimizer\Model\Code $code)
    {
        $this->_codeModel = $code;
    }

    /**
     * Get loaded Code object by Entity
     *
     * @param \Magento\Framework\Model\AbstractModel $entity
     * @return \Magento\GoogleOptimizer\Model\Code
     */
    public function getCodeObjectByEntity(\Magento\Framework\Model\AbstractModel $entity)
    {
        $this->_entity = $entity;

        $this->_checkEntityIsEmpty();
        if ($entity instanceof \Magento\Cms\Model\Page) {
            $this->_codeModel->loadByEntityIdAndType($entity->getId(), $this->_getEntityType());
        } else {
            $this->_codeModel->loadByEntityIdAndType($entity->getId(), $this->_getEntityType(), $entity->getStoreId());
        }

        return $this->_codeModel;
    }

    /**
     * Get Entity Type by Entity object
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function _getEntityType()
    {
        $type = $this->_getTypeString();

        if (empty($type)) {
            throw new \InvalidArgumentException('The model class is not valid');
        }

        return $type;
    }

    /**
     * Get Entity Type string
     *
     * @return string
     */
    protected function _getTypeString()
    {
        $type = '';
        if ($this->_entity instanceof \Magento\Catalog\Model\Category) {
            $type = \Magento\GoogleOptimizer\Model\Code::ENTITY_TYPE_CATEGORY;
        }

        if ($this->_entity instanceof \Magento\Catalog\Model\Product) {
            $type = \Magento\GoogleOptimizer\Model\Code::ENTITY_TYPE_PRODUCT;
        }

        if ($this->_entity instanceof \Magento\Cms\Model\Page) {
            $type = \Magento\GoogleOptimizer\Model\Code::ENTITY_TYPE_PAGE;
        }
        return $type;
    }

    /**
     * Check if Entity is Empty
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    protected function _checkEntityIsEmpty()
    {
        if (!$this->_entity->getId()) {
            throw new \InvalidArgumentException('The model is empty');
        }
        return $this;
    }
}
