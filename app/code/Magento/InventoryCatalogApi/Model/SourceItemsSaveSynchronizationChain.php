<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalogApi\Model;

use Magento\Framework\Exception\LocalizedException;

/**
 * @inheritdoc
 */
class SourceItemsSaveSynchronizationChain implements SourceItemsSaveSynchronizationInterface
{
    /**
     * @var array
     */
    private $definitions;

    /**
     * @var SourceItemsSaveSynchronizationInterface[]|null
     */
    private $sourceItemsSaveSynchronizations;

    /**
     * @param array $definitions
     */
    public function __construct(
        array $definitions = []
    ) {
        $this->validateDefinitions($definitions);
        $this->definitions = $definitions;
    }

    /**
     * @param array $definitions
     * @return void
     * @throws LocalizedException
     */
    private function validateDefinitions(array $definitions): void
    {
        foreach ($definitions as $definition) {
            if (empty($definition['object'])
                || !$definition['object'] instanceof SourceItemsSaveSynchronizationInterface
            ) {
                throw new LocalizedException(
                    __('Parameter "object" must be present and implement SourceItemsSaveSynchronizationInterface.')
                );
            }

            if (empty($definition['sort_order'])) {
                throw new LocalizedException(__('Parameter "sort_order" must be present.'));
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function execute(array $sourceItems): void
    {
        if (null === $this->sourceItemsSaveSynchronizations) {
            $this->initSourceItemsSaveSynchronizations();
        }

        foreach ($this->sourceItemsSaveSynchronizations as $sourceItemSynchronization) {
            $sourceItemSynchronization->execute($sourceItems);
        }
    }

    /**
     * @return void
     */
    private function initSourceItemsSaveSynchronizations():void
    {
        usort($this->definitions, function (array $a, array $b) {
            if ($a['sort_order'] == $b['sort_order']) {
                return 0;
            }
            return ($a['sort_order'] < $b['sort_order']) ? -1 : 1;
        });
        $this->sourceItemsSaveSynchronizations = array_column($this->definitions, 'object');
    }
}
