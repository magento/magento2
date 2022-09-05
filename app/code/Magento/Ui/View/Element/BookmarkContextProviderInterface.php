<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Ui\View\Element;

use Magento\Framework\View\Element\UiComponent\ContextInterface;

interface BookmarkContextProviderInterface
{
    /**
     * Retrieve shared bookmark context object by ui context
     *
     * @param ContextInterface $context
     * @return BookmarkContextInterface
     */
    public function getByUiContext(ContextInterface $context): BookmarkContextInterface;
}
