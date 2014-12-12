<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Model\Resource\Type;

abstract class Db extends \Magento\Framework\Model\Resource\Type\AbstractType
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_entityClass = 'Magento\Framework\Model\Resource\Entity\Table';
    }
}
