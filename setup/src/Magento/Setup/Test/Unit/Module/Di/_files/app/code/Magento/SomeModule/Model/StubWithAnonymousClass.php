<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SomeModule\Model;

use Magento\SomeModule\DummyFactory;

class StubWithAnonymousClass
{
    /**
     * @var DummyFactory
     */
    private $factory;

    public function __construct(DummyFactory $factory)
    {
        $this->factory = $factory;
    }

    public function getSerializable(): \JsonSerializable
    {
        return new class() implements \JsonSerializable {
            /**
             * @inheritDoc
             */
            public function jsonSerialize()
            {
                return [1, 2, 3];
            }
        };
    }
}
