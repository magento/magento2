<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Core\Model;

class EntityFactory implements \Magento\Framework\Data\Collection\EntityFactoryInterface
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager = null;

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param string $className
     * @param array $data
     * @throws \LogicException
     * @return \Magento\Framework\Object
     */
    public function create($className, array $data = [])
    {
        $model = $this->_objectManager->create($className, $data);
        //TODO: fix that when this factory used only for \Magento\Core\Model\Abstract
        //if (!$model instanceof \Magento\Core\Model\Abstract) {
        //    throw new \LogicException($className . ' doesn\'t implement \Magento\Core\Model\Abstract');
        //}
        return $model;
    }
}
