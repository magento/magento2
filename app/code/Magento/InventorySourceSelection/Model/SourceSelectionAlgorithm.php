<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySourceSelection\Model;

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
     * @var string
     */
    private $description;

    /**
     * @param string $code
     * @param string $title
     * @param string $description
     */
    public function __construct(string $code, string $title, string $description)
    {
        $this->code = $code;
        $this->title = $title;
        $this->description = $description;
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

    /**
     * @inheritdoc
     */
    public function getDescription(): string
    {
        return $this->description;
    }
}
