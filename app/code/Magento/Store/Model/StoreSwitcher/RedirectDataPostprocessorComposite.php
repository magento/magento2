<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Store\Model\StoreSwitcher;

/**
 * Store switcher redirect data post-processors collection
 */
class RedirectDataPostprocessorComposite implements RedirectDataPostprocessorInterface
{
    /**
     * @var RedirectDataPostprocessorInterface[]
     */
    private $processors;

    /**
     * @param RedirectDataPostprocessorInterface[] $processors
     */
    public function __construct(array $processors = [])
    {
        $this->processors = $processors;
    }

    /**
     * @inheritdoc
     */
    public function process(ContextInterface $context, array $data): void
    {
        foreach ($this->processors as $processor) {
            $processor->process($context, $data);
        }
    }
}
