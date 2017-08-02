<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Widget\Grid\Row;

/**
 * Grid row url generator factory
 *
 * @api
 */
class UrlGeneratorFactory
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
     * Create new url generator instance
     *
     * @param string $generatorClassName
     * @param array $arguments
     * @return \Magento\Backend\Model\Widget\Grid\Row\UrlGenerator
     * @throws \InvalidArgumentException
     */
    public function createUrlGenerator($generatorClassName, array $arguments = [])
    {
        $rowUrlGenerator = $this->_objectManager->create($generatorClassName, $arguments);
        if (false === $rowUrlGenerator instanceof \Magento\Backend\Model\Widget\Grid\Row\GeneratorInterface) {
            throw new \InvalidArgumentException('Passed wrong parameters');
        }

        return $rowUrlGenerator;
    }
}
