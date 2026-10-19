<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Mview\View;

/**
 * Default walker that exposes a checkpoint after every successfully processed batch.
 */
class CursorAwareChangelogBatchWalker extends ChangelogBatchWalker implements CursorAwareChangelogBatchWalkerInterface
{
}
