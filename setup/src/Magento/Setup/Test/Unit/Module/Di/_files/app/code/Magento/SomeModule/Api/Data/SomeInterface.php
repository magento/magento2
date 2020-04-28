<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SomeModule\Api\Data;

use Magento\Framework\Api\CustomAttributesDataInterface;

interface SomeInterface extends CustomAttributesDataInterface
{
    /**
     * @return \Magento\Eav\Api\Data\AttributeExtensionInterface|null
     */
    public function getExtensionAttributes();
}
