<?php

namespace Liip\CustomerHierarchy\Model\Role\Permission\Checker;

use \Liip\CustomerHierarchy\Model\Role\Permission\CheckerInterface;

class Pool implements PoolInterface
{
    /**
     * @var CheckerInterface[]
     */
    protected $checkers;

    /**
     * @param CheckerInterface[] $checkers
     */
    public function __construct(array $checkers)
    {
        $this->checkers = $checkers;
    }

    /**
     * @param string $type
     * @return CheckerInterface
     */
    public function get(string $type): CheckerInterface
    {
        return $this->checkers[$type];
    }
}
