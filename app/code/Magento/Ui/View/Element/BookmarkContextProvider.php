<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Ui\View\Element;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class BookmarkContextProvider implements BookmarkContextProviderInterface
{
    /**
     * @var ObjectManagerInterface
     */
    private ObjectManagerInterface $objectManager;

    /**
     * @var array
     */
    private array $sharedContext = [];

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @inheritDoc
     */
    public function getByUiContext(ContextInterface $context): BookmarkContextInterface
    {
        $key = $context->getNamespace();
        if (!isset($this->sharedContext[$key])) {
            $this->sharedContext[$key] = $this->objectManager->create(
                BookmarkContextInterface::class,
                ['context' => $context]
            );
        }

        return $this->sharedContext[$key];
    }
}
