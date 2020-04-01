<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryApi\Test\Unit\Model\Directory;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Magento\MediaGalleryApi\Model\Directory\Blacklist;

/**
 * Test the Excluded model
 */
class BlacklistTest extends TestCase
{
    /**
     * @var Blacklist
     */
    private $object;

    /**
     * Initialize basic test class mocks
     */
    protected function setUp(): void
    {
        $this->object = (new ObjectManager($this))->getObject(
            Blacklist::class,
            [
                'patterns' => [
                    'tmp' => '/pub\/media\/tmp/',
                    'captcha' => '/pub\/media\/captcha/'
                ]
            ]
        );
    }

    /**
     * Test if the directory path is blacklisted
     *
     * @param string $path
     * @param bool $isExcluded
     * @dataProvider pathsProvider
     */
    public function testIsBlacklisted(string $path, bool $isExcluded): void
    {
        $this->assertEquals($isExcluded, $this->object->isBlacklisted($path));
    }

    /**
     * Data provider for testIsExcluded
     *
     * @return array
     */
    public function pathsProvider()
    {
        return [
            ['/var/www/html/pub/media/tmp/somedir', true],
            ['/var/www/html/pub/media/wysiwyg/somedir', false]
        ];
    }
}
