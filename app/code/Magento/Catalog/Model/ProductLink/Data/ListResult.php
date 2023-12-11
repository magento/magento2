<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ProductLink\Data;

use Magento\Catalog\Api\Data\ProductLinkInterface;

/**
 * @inheritDoc
 */
class ListResult implements ListResultInterface
{
    /**
     * @var ProductLinkInterface[]|null
     */
    private $result;

    /**
     * @var \Throwable|null
     */
    private $error;

    /**
     * ListResult constructor.
     * @param ProductLinkInterface[]|null $result
     * @param \Throwable|null $error
     */
    public function __construct(?array $result, ?\Throwable $error)
    {
        $this->result = $result;
        $this->error = $error;
        if ($this->result === null && $this->error === null) {
            throw new \InvalidArgumentException('Result must either contain values or an error.');
        }
    }

    /**
     * @inheritDoc
     */
    public function getResult(): ?array
    {
        return $this->result;
    }

    /**
     * @inheritDoc
     */
    public function getError(): ?\Throwable
    {
        return $this->error;
    }
}
