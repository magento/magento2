<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\ReportXml\DB;

use Magento\Framework\ObjectManagerInterface;

/**
 * Factory class for @see \Magento\Analytics\ReportXml\DB\SelectBuilder
 */
class SelectBuilderFactory
{
    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * SelectBuilderFactory constructor.
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return SelectBuilder
     */
    public function create(array $data = [])
    {
        return $this->objectManager->create(SelectBuilder::class, $data);
    }
}
