<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Model\SourceSelection;

use Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionAlgorithmInterface;

/**
 * @inheritdoc
 */
class SourceSelectionAlgorithm implements SourceSelectionAlgorithmInterface
{
    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $title;

    /**
     * SourceSelectionAlgorithm constructor.
     * @param string $code
     * @param string $title
     */
    public function __construct(string $code, string $title)
    {
        $this->code = $code;
        $this->title = $title;
    }

    /**
     * @inheritdoc
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @inheritdoc
     */
    public function getTitle(): string
    {
        return $this->title;
    }
}
