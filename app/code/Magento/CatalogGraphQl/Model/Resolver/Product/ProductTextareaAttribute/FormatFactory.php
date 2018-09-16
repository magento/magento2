<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Product\ProductTextareaAttribute;

use Magento\Framework\ObjectManagerInterface;

class FormatFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param string $formatIdentifier
     * @param array $data
     * @return FormatInterface
     */
    public function create(string $formatIdentifier, $data = []) : FormatInterface
    {
        $formatClassName = 'Magento\CatalogGraphQl\Model\Resolver\Product\ProductTextareaAttribute\\' . ucfirst($formatIdentifier);
        $formatInstance = $this->objectManager->create($formatClassName, $data);
        if (false == $formatInstance instanceof FormatInterface) {
            throw new \InvalidArgumentException(
                $formatInstance . ' is not instance of \Magento\CatalogGraphQl\Model\Resolver\Product\ProductTextareaAttribute\FormatInterface'
            );
        }
        return $formatInstance;
    }
}
