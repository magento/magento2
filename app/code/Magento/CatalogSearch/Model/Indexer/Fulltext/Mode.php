<?php

declare(strict_types=1);

namespace Magento\CatalogSearch\Model\Indexer\Fulltext;

class Mode
{
    const STATE_FULL = 'full';
    const STATE_PARTIAL = 'partial';

    /**
     * @var string
     */
    private $state = null;

    /**
     * @return void
     */
    public function setFullIndexationMode(): void
    {
        $this->state = self::STATE_FULL;
    }

    /**
     * @return void
     */
    public function setPartialIndexationMode(): void
    {
        $this->state = self::STATE_PARTIAL;
    }

    /**
     * @return bool
     */
    public function getIsFullIndexationMode(): bool
    {
        return $this->state === self::STATE_FULL;
    }

    /**
     * @return bool
     */
    public function getIsPartialIndexationMode(): bool
    {
        return $this->state === self::STATE_PARTIAL;
    }
}
