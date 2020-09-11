<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Translation\Model\Inline;

use Magento\Framework\Phrase\RendererInterface;

/**
 * Inline Translate phrase renderer for DataProvider.
 */
class Renderer implements RendererInterface
{
    /**
     * @inheritdoc
     */
    public function render(array $source, array $arguments)
    {
        return end($source);
    }
}
