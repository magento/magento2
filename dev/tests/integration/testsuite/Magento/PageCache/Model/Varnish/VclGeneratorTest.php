<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\PageCache\Model\Varnish;

use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class VclGeneratorTest extends TestCase
{

    /**
     * @var VclGenerator
     */
    private $generator;

    public function setUp(): void
    {
        $vclGeneratorParams = [
            'backendHost' => 'example.com',
            'backendPort' => '8080',
            'accessList' => ['127.0.0.1', '192.168.0.1', '127.0.0.2'],
            'designExceptions' => ['_' => [
                'regexp' => '/firefox/i',
                'value' => 'Magento/blank'
            ]],
            'sslOffloadedHeader' => 'X-Forwarded-Proto',
            'gracePeriod' => 1234
        ];

        $generatorFactory = Bootstrap::getObjectManager()->get(VclGeneratorFactory::class);
        $this->generator = $generatorFactory->create($vclGeneratorParams);
    }

    public function testGetVarnish7VclFile()
    {
        $expected = 'Varnish version is 7.0';
        $vclContent = $this->generator->generateVcl(VclTemplateLocator::VARNISH_SUPPORTED_VERSION_7);

        $this->assertStringContainsString($expected, $vclContent);
    }
}
