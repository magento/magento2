<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Product\ProductTextAttribute;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class FormatList
 * @package Magento\CatalogGraphQl\Model\Resolver\Product\ProductTextAttribute
 */
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
    public function getFormatByIdentifier(string $formatIdentifier) : FormatInterface
    {
        if (!isset($this->formats[$formatIdentifier])) {
            throw new GraphQlInputException(__('Format %1 does not exist.', [$formatIdentifier]));
        }
        $formatInstance = $this->objectManager->get($this->formats[$formatIdentifier]);

        return $formatInstance;
    }
}
