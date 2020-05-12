<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module\I18n\Dictionary\Options;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Setup\Module\I18n\Dictionary\Options\ResolverFactory;
use PHPUnit\Framework\TestCase;

class ResolverFactoryTest extends TestCase
{
    public function testCreate()
    {
        $objectManagerHelper = new ObjectManager($this);
        /** @var ResolverFactory $resolverFactory */
        $resolverFactory = $objectManagerHelper
            ->getObject(ResolverFactory::class);
        $this->assertInstanceOf(
            ResolverFactory::DEFAULT_RESOLVER,
            $resolverFactory->create('some_dir', true)
        );
    }

    public function testCreateException()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('stdClass doesn\'t implement ResolverInterface');
        $objectManagerHelper = new ObjectManager($this);
        /** @var ResolverFactory $resolverFactory */
        $resolverFactory = $objectManagerHelper->getObject(
            ResolverFactory::class,
            [
                'resolverClass' => 'stdClass'
            ]
        );
        $resolverFactory->create('some_dir', true);
    }
}
