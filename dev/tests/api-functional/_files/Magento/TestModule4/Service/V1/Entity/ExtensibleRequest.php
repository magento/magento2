<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
