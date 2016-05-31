<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Url;

/**
 * Composite URL modifier.
 */
class ModifierComposite implements ModifierInterface
{
    /**
     * @var ModifierInterface[]
     */
    private $modifiers;

    /**
     * @param ModifierInterface[] $modifiers
     */
    public function __construct(array $modifiers = [])
    {
        $this->modifiers = $modifiers;
    }

    /**
     * {@inheritdoc}
     */
    public function execute($url, $mode = ModifierInterface::MODE_ENTIRE)
    {
        foreach ($this->modifiers as $modifier) {
            $url = $modifier->execute($url, $mode);
        }

        return $url;
    }
}
