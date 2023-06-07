<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Model\StoreSwitcher;

/**
 * Store switcher redirect data pre-processors collection
 */
class RedirectDataPreprocessorComposite implements RedirectDataPreprocessorInterface
{
    /**
     * @var RedirectDataPreprocessorInterface[]
     */
    private $processors;

    /**
     * @param RedirectDataPreprocessorInterface[] $processors
     */
    public function __construct(array $processors = [])
    {
        $this->processors = $processors;
    }

    /**
     * @inheritdoc
     */
    public function process(ContextInterface $context, array $data): array
    {
        foreach ($this->processors as $processor) {
            $data = $processor->process($context, $data);
        }

        return $data;
    }
}
