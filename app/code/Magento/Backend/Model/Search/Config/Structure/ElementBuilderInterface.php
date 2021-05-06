<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Model\Search\Config\Structure;

use Magento\Config\Model\Config\StructureElementInterface;

/**
 * Element builder interface
 *
 * @api
 */
interface ElementBuilderInterface
{
    /**
     * @param StructureElementInterface $structureElement
     * @return array
     */
    public function build(StructureElementInterface $structureElement);
}
