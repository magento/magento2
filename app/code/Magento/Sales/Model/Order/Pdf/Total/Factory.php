<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Pdf\Total;

class Factory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Default total model
     *
     * @var string
     */
    protected $_defaultTotalModel = 'Magento\Sales\Model\Order\Pdf\Total\DefaultTotal';

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create instance of a total model
     *
     * @param string|null $class
     * @param array $arguments
     * @return \Magento\Sales\Model\Order\Pdf\Total\DefaultTotal
     * @throws \Magento\Framework\Model\Exception
     */
    public function create($class = null, $arguments = [])
    {
        $class = $class ?: $this->_defaultTotalModel;
        if (!is_a($class, 'Magento\Sales\Model\Order\Pdf\Total\DefaultTotal', true)) {
            throw new \Magento\Framework\Model\Exception(
                sprintf(
                    'The PDF total model %s must be or extend \Magento\Sales\Model\Order\Pdf\Total\DefaultTotal.',
                    $class
                )
            );
        }
        return $this->_objectManager->create($class, $arguments);
    }
}
