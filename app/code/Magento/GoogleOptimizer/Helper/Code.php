<?php
/**
 * Google Optimizer Scripts Helper
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleOptimizer\Helper;

/**
 * @api
 * @since 2.0.0
 */
class Code
{
    /**
     * @var \Magento\GoogleOptimizer\Model\Code
     * @since 2.0.0
     */
    protected $_codeModel;

    /**
     * @var \Magento\Framework\Model\AbstractModel
     * @since 2.0.0
     */
    protected $_entity;

    /**
     * @param \Magento\GoogleOptimizer\Model\Code $code
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    protected function _checkEntityIsEmpty()
    {
        if (!$this->_entity->getId()) {
            throw new \InvalidArgumentException('The model is empty');
        }
        return $this;
    }
}
