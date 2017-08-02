<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Url;

/**
 * Composite URL modifier.
 * @since 2.1.0
 */
class ModifierComposite implements ModifierInterface
{
    /**
     * @var ModifierInterface[]
     * @since 2.1.0
     */
    private $modifiers;

    /**
     * @param ModifierInterface[] $modifiers
     * @since 2.1.0
     */
    public function __construct(array $modifiers = [])
    {
        $this->modifiers = $modifiers;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function execute($url, $mode = ModifierInterface::MODE_ENTIRE)
    {
        foreach ($this->modifiers as $modifier) {
            $url = $modifier->execute($url, $mode);
        }

        return $url;
    }
}
