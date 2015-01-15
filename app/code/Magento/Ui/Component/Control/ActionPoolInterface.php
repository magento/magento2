<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Control;

use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Interface ActionPoolInterface
 */
interface ActionPoolInterface
{
    /**
     * Add button
     *
     * @param string $key
     * @param array $data
     * @param UiComponentInterface $context
     * @return void
     */
    public function add($key, array $data, UiComponentInterface $context);

    /**
     * Remove button
     *
     * @param string $key
     * @return void
     */
    public function remove($key);

    /**
     * Update button
     *
     * @param string $key
     * @param array $data
     * @return void
     */
    public function update($key, array $data);
}
