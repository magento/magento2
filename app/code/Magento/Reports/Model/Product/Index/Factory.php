<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Model\Product\Index;

/**
 * Class Factory
 *
 * @api
 * @since 100.0.2
 */
class Factory
{
    const TYPE_COMPARED = 'compared';

    const TYPE_VIEWED = 'viewed';

    /**
     * @var array
     */
    protected $_typeClasses = [
        self::TYPE_COMPARED => \Magento\Reports\Model\Product\Index\Compared::class,
        self::TYPE_VIEWED => \Magento\Reports\Model\Product\Index\Viewed::class,
    ];

    /**
     * @var \Magento\Reports\Model\Product\Index\Abstract[]
     */
    protected $_instances;

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
     * Get Instance
     *
     * @param string $type
     *
     * @return \Magento\Reports\Model\Product\Index\Abstract
     * @throws \InvalidArgumentException
     */
    public function get($type)
    {
        if (!isset($this->_instances[$type])) {
            if (!isset($this->_typeClasses[$type])) {
                throw new \InvalidArgumentException("{$type} is not index model");
            }
            $this->_instances[$type] = $this->_objectManager->create($this->_typeClasses[$type]);
        }
        return $this->_instances[$type];
    }
}
