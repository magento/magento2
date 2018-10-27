<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\ReportXml;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class IteratorFactory
 */
class IteratorFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var string
     */
    private $defaultIteratorName;

    /**
     * IteratorFactory constructor.
     *
     * @param ObjectManagerInterface $objectManager
     * @param string $defaultIteratorName
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        $defaultIteratorName = \IteratorIterator::class
    ) {
        $this->objectManager = $objectManager;
        $this->defaultIteratorName = $defaultIteratorName;
    }

    /**
     * Creates instance of the result iterator with the query result as an input
     * Result iterator can be changed through report configuration
     * <report name="reportName" iterator="Iterator\Class\Name">
     *     < ...
     * </report>
     * Uses IteratorIterator by default
     *
     * @param \Traversable $result
     * @param string|null $iteratorName
     * @return \IteratorIterator
     */
    public function create(\Traversable $result, $iteratorName = null)
    {
        return $this->objectManager->create(
            $iteratorName ?: $this->defaultIteratorName,
            [
                'iterator' => $result
            ]
        );
    }
}
