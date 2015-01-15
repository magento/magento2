<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestModule4\Service\V1\Entity;

class ExtensibleRequest extends \Magento\Framework\Model\AbstractExtensibleModel
    implements ExtensibleRequestInterface
{
    public function getName()
    {
        return $this->getData("name");
    }
}
