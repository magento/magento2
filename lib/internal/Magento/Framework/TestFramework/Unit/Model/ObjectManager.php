<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\TestFramework\Unit\Model;

/**
 * ObjectManager which throws an exception when its methods are called
 */
class ObjectManager implements \Magento\Framework\ObjectManagerInterface
{
    /**
     * Throws an exception for any call of this method.
     *
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function create($type, array $arguments = [])
    {
        $this->throwNotMockedObjectManagerException();
    }

    /**
     * Throws an exception for any call of this method.
     *
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function get($type)
    {
        $this->throwNotMockedObjectManagerException();
    }

    /**
     * Throws an exception for any call of this method.
     *
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function configure(array $configuration)
    {
        $this->throwNotMockedObjectManagerException();
    }

    /**
     * Throws an exception when ObjectManager has been called.
     *
     * This exception notifies that a developer should mock the ObjectManager or avoid its usage.
     *
     * @return void
     * @throws \RuntimeException
     */
    private function throwNotMockedObjectManagerException()
    {
        throw new \RuntimeException('ObjectManager shouldn\'t be used in unit tests without mocking');
    }
}
