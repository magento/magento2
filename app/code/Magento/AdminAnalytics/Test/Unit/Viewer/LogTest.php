<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminAnalytics\Test\Unit\Viewer;

use Magento\AdminAnalytics\Model\Viewer\Log;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class LogTest extends TestCase
{
    /** @var Log */
    private $log;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->log = $objectManager->getObject(Log::class);
    }

    /**
     * @dataProvider provideDataToGetId
     *
     * @param int|null $value
     * @param int|null $expected
     */
    public function testGetId(int $value = null, int $expected = null)
    {
        $this->log->setId($value);

        $actualValue = $this->log->getId();

        $this->assertSame($expected, $actualValue);
    }

    /**
     * @dataProvider provideGetLastViewVersion
     *
     * @param mixed $value
     * @param string|null $expected
     */
    public function testGetLastViewVersion($value = null, string $expected = null)
    {
        $this->log->setData('last_viewed_in_version', $value);

        $actualValue = $this->log->getLastViewVersion();

        $this->assertSame($expected, $actualValue);
    }

    public function provideDataToGetId()
    {
        return [
            [123, 123],
            [null, null],
        ];
    }

    public function provideGetLastViewVersion()
    {
        return [
            ['test_fake', 'test_fake'],
            [null, null],
        ];
    }
}
