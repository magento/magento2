<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Search\Config\Structure;

use Magento\Config\Model\Config\StructureElementInterface;

interface ElementBuilderInterface
{
    /**
     * @param StructureElementInterface $structureElement
     * @return array
     */
    public function build(StructureElementInterface $structureElement);
}
