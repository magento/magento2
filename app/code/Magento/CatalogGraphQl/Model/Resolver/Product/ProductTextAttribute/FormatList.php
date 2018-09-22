<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Product\ProductTextAttribute;

use Magento\Framework\ObjectManagerInterface;

class FormatList
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var string
     */
    private $formats;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param array $formats
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        array $formats
    ) {
        $this->objectManager = $objectManager;
        $this->formats = $formats;
    }

    /**
     * @param string $formatIdentifier
     * @return FormatInterface
     */
    public function create(string $formatIdentifier) : FormatInterface
    {
        $formatClassName = 'Magento\CatalogGraphQl\Model\Resolver\Product\ProductTextAttribute\\' . ucfirst($formatIdentifier);
        $formatInstance = $this->objectManager->get($formatClassName);

        return $formatInstance;
    }
}
