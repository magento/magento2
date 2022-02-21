<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Model\Search\Request;

/**
 * Search requests configuration composite modifier
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
    public function __construct(
        array $modifiers = []
    ) {
        foreach ($modifiers as $modifier) {
            if (!$modifier instanceof ModifierInterface) {
                throw new \InvalidArgumentException(
                    get_class($modifier) . ' must implement ' . ModifierInterface::class
                );
            }
        }
        $this->modifiers = $modifiers;
    }

    /**
     * @inheritdoc
     */
    public function modify(array $requests): array
    {
        foreach ($this->modifiers as $modifier) {
            $requests = $modifier->modify($requests);
        }
        return $requests;
    }
}
