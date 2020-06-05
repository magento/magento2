<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallery\Test\Unit\Model\Directory;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\MediaGallery\Model\Directory\IsBlacklisted;
use Magento\MediaGalleryApi\Model\BlacklistPatternsConfigInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for IsBlacklisted
 */
class IsBlacklistedTest extends TestCase
{
    /**
     * @var IsBlacklisted
     */
    private $object;

    /**
     * @var BlacklistPatternsConfigInterface|MockObject
     */
    private $config;

    /**
     * Initialize basic test class mocks
     */
    protected function setUp(): void
    {
        $this->config = $this->getMockBuilder(BlacklistPatternsConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->config->expects($this->at(0))->method('get')->willReturn([
            'tmp' => '/pub\/media\/tmp/',
            'captcha' => '/pub\/media\/captcha/'
        ]);
        $this->object = (new ObjectManager($this))->getObject(IsBlacklisted::class, [
            'config' => $this->config
        ]);
    }

    /**
     * Test if the directory path is blacklisted
     *
     * @param string $path
     * @param bool $isExcluded
     * @dataProvider pathsProvider
     */
    public function testExecute(string $path, bool $isExcluded): void
    {
        $this->assertEquals($isExcluded, $this->object->execute($path));
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
