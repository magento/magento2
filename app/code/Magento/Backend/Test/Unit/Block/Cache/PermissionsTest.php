<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Unit\Block\Cache;

use Magento\Backend\Block\Cache\Permissions;
use Magento\Framework\Authorization;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Test for Permissions
 */
class PermissionsTest extends TestCase
{
    /**
     * @var Permissions
     */
    private $permissions;

    /**
     * @var AuthorizationInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $mockAuthorization;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->mockAuthorization = $this->getMockBuilder(Authorization::class)
            ->disableOriginalConstructor()
            ->setMethods(['isAllowed'])
            ->getMock();

        $this->permissions = new Permissions($this->mockAuthorization);
    }

    public function testHasAccessToFlushCatalogImages()
    {
        $this->mockAuthorization->expects($this->atLeastOnce())
            ->method('isAllowed')
            ->with('Magento_Backend::flush_catalog_images')
            ->willReturn(true);

        $this->assertTrue($this->permissions->hasAccessToFlushCatalogImages());
    }

    public function testHasAccessToFlushJsCss()
    {
        $this->mockAuthorization->expects($this->atLeastOnce())
            ->method('isAllowed')
            ->with('Magento_Backend::flush_js_css')
            ->willReturn(true);

        $this->assertTrue($this->permissions->hasAccessToFlushJsCss());
    }

    public function testHasAccessToFlushStaticFiles()
    {
        $this->mockAuthorization->expects($this->atLeastOnce())
            ->method('isAllowed')
            ->with('Magento_Backend::flush_static_files')
            ->willReturn(true);

        $this->assertTrue($this->permissions->hasAccessToFlushStaticFiles());
    }
}
