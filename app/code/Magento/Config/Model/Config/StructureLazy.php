<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Config\Model\Config;

use Magento\Framework\ObjectManager\NoninterceptableInterface;

/**
 * @inheritdoc
 */
class StructureLazy extends Structure implements NoninterceptableInterface
{
    /**
     * @var Structure\Data
     */
    private $structureData;

    /**
     * @param \Magento\Config\Model\Config\Structure\Data $structureData
     * @param \Magento\Config\Model\Config\Structure\Element\Iterator\Tab $tabIterator
     * @param \Magento\Config\Model\Config\Structure\Element\FlyweightFactory $flyweightFactory
     * @param ScopeDefiner $scopeDefiner
     */
    public function __construct(
        \Magento\Config\Model\Config\Structure\Data $structureData,
        \Magento\Config\Model\Config\Structure\Element\Iterator\Tab $tabIterator,
        \Magento\Config\Model\Config\Structure\Element\FlyweightFactory $flyweightFactory,
        ScopeDefiner $scopeDefiner
    ) {
        $this->_tabIterator = $tabIterator;
        $this->_flyweightFactory = $flyweightFactory;
        $this->_scopeDefiner = $scopeDefiner;
        $this->structureData = $structureData;
    }

    /**
     * @inheritdoc
     */
    public function getElement($path)
    {
        $this->loadStructureData();
        return parent::getElement($path);
    }

    /**
     * @inheritdoc
     */
    public function getTabs()
    {
        $this->loadStructureData();
        return parent::getTabs();
    }

    /**
     * @inheritdoc
     */
    public function getSectionList()
    {
        $this->loadStructureData();
        return parent::getSectionList();
    }

    /**
     * @inheritdoc
     */
    public function getElementByConfigPath($path)
    {
        $this->loadStructureData();
        return parent::getElementByConfigPath($path);
    }

    /**
     * @inheritdoc
     */
    public function getFirstSection()
    {
        $this->loadStructureData();
        return parent::getTabs();
    }

    /**
     * @inheritdoc
     */
    public function getElementByPathParts(array $pathParts)
    {
        $this->loadStructureData();
        return parent:: getElementByPathParts($pathParts);
    }

    /**
     * @inheritdoc
     */
    public function getFieldPathsByAttribute($attributeName, $attributeValue)
    {
        $this->loadStructureData();
        return parent::getFieldPathsByAttribute($attributeName, $attributeValue);
    }

    /**
     * @inheritdoc
     */
    public function getFieldPaths()
    {
        $this->loadStructureData();
        return parent::getFieldPaths();
    }

    /**
     * Load data
     */
    private function loadStructureData()
    {
        if ($this->_data === null) {
            $this->_data = $this->structureData->get();
        }
    }
}
