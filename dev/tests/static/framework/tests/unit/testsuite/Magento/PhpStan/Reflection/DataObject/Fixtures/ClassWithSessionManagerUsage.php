<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\PhpStan\Reflection\DataObject\Fixtures;

use Magento\Framework\Session\SessionManager;

/**
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class ClassWithSessionManagerUsage
{
    /**
     * @var SessionManager
     */
    private $container;

    /**
     * @param SessionManager $sessionManager
     */
    public function __construct(SessionManager $sessionManager)
    {
        $this->container = $sessionManager;
    }

    /**
     * Do Magic Stuff.
     *
     * 'get' - args: $index[optional] - string|int, return: mixed;
     * 'set' - args: $value - mixed, return: self;
     * 'uns' - args: -, return: self;
     * 'has' - args: -, return: bool;
     */
    public function doStuff(): void
    {
        $this->container->getBaz(
            $this->container->unsFoo(
                $this->container->setBaz()
            )
        );
        $this->container->hasFoo(
            $this->container->setStuff()
        );

        $this->container->getSomething($this->container->hasFoo());
    }

    /**
     * Correct usage.
     *
     * @return void
     */
    public function doCorrectStuff(): void
    {
        $a = $this->container->getSomething('1');

        if ($this->container->hasSomething()) {
            $this->container->setSomething2($a);
        } else {
            $this->container->unsetSomething();
            $this->container->setSometing2($this->container->getStuff());
        }
    }
}
