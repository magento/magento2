<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Model\Search\Config\Structure\Element\Builder;

use Magento\Backend\Model\Search\Config\Structure\ElementBuilderInterface;
use Magento\Config\Model\Config\StructureElementInterface;

class Field implements ElementBuilderInterface
{
    /**
     * @inheritdoc
     */
    public function build(StructureElementInterface $structureElement)
    {
        $elementPathParts = explode('/', $structureElement->getPath());
        return [
            'section' => $elementPathParts[0],
            'group'   => $elementPathParts[1],
            'field'   => $structureElement->getId(),
        ];
    }
}
