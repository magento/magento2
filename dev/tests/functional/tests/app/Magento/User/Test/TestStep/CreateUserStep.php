<?php
/**
 * Copyright © 2013-2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Test\TestStep;

use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Create new user.
 */
class CreateUserStep implements TestStepInterface
{
    /**
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * @var array
     */
    private $userParams;

    /**
     * @param FixtureFactory $fixtureFactory
     * @param array $user
     */
    public function __construct(FixtureFactory $fixtureFactory, $user = [])
    {
        $this->fixtureFactory = $fixtureFactory;
        $this->userParams = $user;
    }

    /**
     * Run step flow.
     *
     * @return array
     */
    public function run()
    {
        $arguments = [];
        if (isset($this->userParams['dataset'])) {
            $arguments['dataset'] = trim($this->userParams['dataset']);
        }
        $user = $this->fixtureFactory->createByCode('user', $this->userParams);
        $user->persist();

        return ['user' => $user];
    }
}
