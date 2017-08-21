<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Search\Config\Structure;

use Magento\Config\Model\Config\Structure\ElementNewInterface;

interface ElementBuilderInterface
{
    /**
     * @param ElementNewInterface $structureElement
     * @return array
     */
    public function build(ElementNewInterface $structureElement);
}
