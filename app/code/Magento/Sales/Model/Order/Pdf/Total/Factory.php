<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Pdf\Total;

/**
 * Class \Magento\Sales\Model\Order\Pdf\Total\Factory
 *
 * @since 2.0.0
 */
class Factory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $_objectManager;

    /**
     * Default total model
     *
     * @var string
     * @since 2.0.0
     */
    protected $_defaultTotalModel = \Magento\Sales\Model\Order\Pdf\Total\DefaultTotal::class;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @since 2.0.0
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
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function create($class = null, $arguments = [])
    {
        $class = $class ?: $this->_defaultTotalModel;
        if (!is_a($class, \Magento\Sales\Model\Order\Pdf\Total\DefaultTotal::class, true)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(
                    'The PDF total model %1 must be or extend \Magento\Sales\Model\Order\Pdf\Total\DefaultTotal.',
                    $class
                )
            );
        }
        return $this->_objectManager->create($class, $arguments);
    }
}
