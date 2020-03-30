<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallery\Test\Unit\Model\Directory;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Magento\MediaGallery\Model\Directory\Excluded;

/**
 * Test the Excluded model
 */
class ExcludedTest extends TestCase
{
    /**
     * @var Excluded
     */
    private $object;

    /**
     * Initialize basic test class mocks
     */
    protected function setUp(): void
    {
        $this->object = (new ObjectManager($this))->getObject(
            Excluded::class,
            [
                'patterns' => [
                    'tmp' => '/pub\/media\/tmp/',
                    'captcha' => '/pub\/media\/captcha/'
                ]
            ]
        );
    }

    /**
     * Test is directory path excluded
     *
     * @param string $path
     * @param bool $isExcluded
     * @dataProvider pathsProvider
     */
    public function testIsExcluded(string $path, bool $isExcluded): void
    {
        $this->assertEquals($isExcluded, $this->object->isExcluded($path));
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
