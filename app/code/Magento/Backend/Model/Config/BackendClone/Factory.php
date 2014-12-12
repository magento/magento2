<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/**
 * System configuration clone model factory
 */
namespace Magento\Backend\Model\Config\BackendClone;

class Factory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create new clone model
     *
     * @param string $cloneModel
     * @return mixed
     */
    public function create($cloneModel)
    {
        return $this->_objectManager->create($cloneModel);
    }
}
