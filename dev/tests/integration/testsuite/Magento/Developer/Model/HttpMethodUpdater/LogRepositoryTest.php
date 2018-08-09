<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Developer\Model\HttpMethodUpdater;

use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;

class LogRepositoryTest extends TestCase
{
    /**
     * @var LogRepository
     */
    private $repo;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->repo = Bootstrap::getObjectManager()->get(LogRepository::class);
    }

    /**
     * Test adding a log.
     */
    public function testLog()
    {
        $class = 'ActionClass';
        $method = 'GET';

        $this->repo->log(new Log($class, $method));

        $found = $this->repo->findLogged();
        $this->assertCount(1, $found);
        $this->assertEquals($class, $found[0]->getActionClass());
        $this->assertCount(1, $found[0]->getMethods());
        $this->assertEquals($method, $found[0]->getMethods()[0]);
    }

    /**
     * Test filtering existing logs.
     */
    public function testFindLogged()
    {
        $c1 = 'ActionClass';
        $method11 = 'GET';
        $c2 = 'ActionClass2';
        $method21 = 'GET';
        $method22 = 'POST';

        $this->repo->log(new Log($c1, $method11));
        $this->repo->log(new Log($c1, $method11));
        $this->repo->log(new Log($c2, $method21));
        $this->repo->log(new Log($c2, $method22));

        $found = $this->repo->findLogged();
        $this->assertCount(2, $found);
        foreach ($found as $logged) {
            if ($logged->getActionClass() === $c1) {
                $this->assertCount(1, $logged->getMethods());
                $this->assertEquals($method11, $logged->getMethods()[0]);
            } elseif ($logged->getActionClass() === $c2) {
                $this->assertCount(2, $logged->getMethods());
                $this->assertCount(
                    2,
                    array_intersect(
                        [$method21, $method22],
                        $logged->getMethods()
                    )
                );
            } else {
                $this->fail('Invalid logged records returned');
            }
        }

        $found = $this->repo->findLogged(false);
        $this->assertCount(1, $found);
        $this->assertEquals($c1, $found[0]->getActionClass());
    }
}
