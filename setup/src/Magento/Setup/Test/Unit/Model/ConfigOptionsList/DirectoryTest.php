<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Model\ConfigOptionsList;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Setup\Option\BooleanConfigOption;
use Magento\Setup\Model\ConfigOptionsList\Cache;
use Magento\Setup\Model\ConfigOptionsList\Directory as DirectoriesConfigOptionsList;
use PHPUnit\Framework\TestCase;

class DirectoryTest extends TestCase
{
    /**
     * @var Cache
     */
    private $configOptionsList;

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfigMock;

    private $expectedEnabled = ['directories' => ['document_root_is_pub' => true]];

    private $expectedDisabled = ['directories' => ['document_root_is_pub' => false]];

    private $expectedEmpty = [];

    /**
     * Tests setup
     */
    protected function setUp(): void
    {
        $this->deploymentConfigMock = $this->createMock(DeploymentConfig::class);
        $this->configOptionsList = new DirectoriesConfigOptionsList();
    }

    /**
     * testGetOptions
     */
    public function testGetOptions(): void
    {
        $options = $this->configOptionsList->getOptions();
        $this->assertCount(1, $options);
        $this->assertArrayHasKey(0, $options);
        $this->assertInstanceOf(BooleanConfigOption::class, $options[0]);
        $this->assertEquals('document-root-is-pub', $options[0]->getName());
    }

    /**
     * @dataProvider configOptionProvider
     *
     * @param string|null $value
     * @param array $config
     */
    public function testCreateConfig(?string $value, array $config): void
    {
        $configData = $this->configOptionsList->createConfig(
            [DirectoriesConfigOptionsList::INPUT_KEY_DOCUMENT_ROOT_IS_PUB => $value],
            $this->deploymentConfigMock
        );

        $this->assertEquals($config, $configData->getData());
    }

    /**
     * @return array[]
     */
    public function configOptionProvider(): array
    {
        return [
            ['value' => '0', 'config' => $this->expectedDisabled],
            ['value' => 'false', 'config' => $this->expectedDisabled],
            ['value' => 'no', 'config' => $this->expectedDisabled],
            ['value' => '1', 'config' => $this->expectedEnabled],
            ['value' => 'true', 'config' => $this->expectedEnabled],
            ['value' => 'yes', 'config' => $this->expectedEnabled],
            ['value' => null, 'config' => $this->expectedEmpty],
        ];
    }
}
