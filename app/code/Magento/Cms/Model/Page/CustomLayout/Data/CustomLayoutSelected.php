<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Cms\Model\Page\CustomLayout\Data;

/**
 * @inheritDoc
 */
class CustomLayoutSelected implements CustomLayoutSelectedInterface
{
    /**
     * @var int
     */
    private $pageId;

    /**
     * @var string
     */
    private $layoutFile;

    /**
     * @param int $pageId
     * @param string $layoutFile
     */
    public function __construct(int $pageId, string $layoutFile)
    {
        $this->pageId = $pageId;
        $this->layoutFile = $layoutFile;
    }

    /**
     * @inheritDoc
     */
    public function getPageId(): int
    {
        return $this->pageId;
    }

    /**
     * @inheritDoc
     */
    public function getLayoutFileId(): string
    {
        return $this->layoutFile;
    }
}
