<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\TestModule4\Service\V1\Entity;

interface ExtensibleRequestInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return int|null
     */
    public function getEntityId();
}
