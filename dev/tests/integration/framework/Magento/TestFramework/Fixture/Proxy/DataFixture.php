<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Fixture\Proxy;

use Magento\Framework\DataObject;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;

/**
 * Class based data fixture
 */
class DataFixture implements DataFixtureInterface
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var string
     */
    private $className;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param string $className
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        string $className
    ) {
        $this->objectManager = $objectManager;
        $this->className = $className;
    }

    /**
     * @inheritdoc
     */
    public function apply(DataObject $data): ?DataObject
    {
        return $this->getInstance()->apply($data);
    }

    /**
     * @inheritdoc
     */
    public function revert(?DataObject $data): void
    {
        $fixture = $this->getInstance();
        if ($fixture instanceof RevertibleDataFixtureInterface) {
            $fixture->revert($data);
        }
    }

    /**
     * Get fixture class instance
     *
     * @return \Magento\TestFramework\Fixture\DataFixtureInterface
     */
    private function getInstance(): \Magento\TestFramework\Fixture\DataFixtureInterface
    {
        return $this->objectManager->create($this->className);
    }
}
