<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\DataProvider\Modifier;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class Pool
 * @since 2.1.0
 */
class Pool implements \Magento\Ui\DataProvider\Modifier\PoolInterface
{
    /**
     * @var array
     * @since 2.1.0
     */
    protected $modifiers = [];

    /**
     * @var array
     * @since 2.1.0
     */
    protected $modifiersInstances = [];

    /**
     * @var ModifierFactory
     * @since 2.1.0
     */
    protected $factory;

    /**
     * @param ModifierFactory $factory
     * @param array $modifiers
     * @since 2.1.0
     */
    public function __construct(
        ModifierFactory $factory,
        array $modifiers
    ) {
        $this->factory = $factory;
        $this->modifiers = $this->sort($modifiers);
    }

    /**
     * Retrieve modifiers
     *
     * @return array
     * @since 2.1.0
     */
    public function getModifiers()
    {
        return $this->modifiers;
    }

    /**
     * Retrieve modifiers instantiated
     *
     * @return ModifierInterface[]
     * @throws LocalizedException
     * @since 2.1.0
     */
    public function getModifiersInstances()
    {
        if ($this->modifiersInstances) {
            return $this->modifiersInstances;
        }

        foreach ($this->modifiers as $modifier) {
            if (empty($modifier['class'])) {
                throw new LocalizedException(__('Parameter "class" must be present.'));
            }

            if (empty($modifier['sortOrder'])) {
                throw new LocalizedException(__('Parameter "sortOrder" must be present.'));
            }

            $this->modifiersInstances[$modifier['class']] = $this->factory->create($modifier['class']);
        }

        return $this->modifiersInstances;
    }

    /**
     * Sorting modifiers according to sort order
     *
     * @param array $data
     * @return array
     * @since 2.1.0
     */
    protected function sort(array $data)
    {
        usort($data, function (array $a, array $b) {
            $a['sortOrder'] = $this->getSortOrder($a);
            $b['sortOrder'] = $this->getSortOrder($b);

            if ($a['sortOrder'] == $b['sortOrder']) {
                return 0;
            }

            return ($a['sortOrder'] < $b['sortOrder']) ? -1 : 1;
        });

        return $data;
    }

    /**
     * Retrieve sort order from array
     *
     * @param array $variable
     * @return int
     * @since 2.1.0
     */
    protected function getSortOrder(array $variable)
    {
        return !empty($variable['sortOrder']) ? $variable['sortOrder'] : 0;
    }
}
