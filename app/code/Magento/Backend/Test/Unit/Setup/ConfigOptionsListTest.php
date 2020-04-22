<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\Setup;

use Magento\Backend\Setup\ConfigOptionsList;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\Data\ConfigData;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Setup\Option\AbstractConfigOption;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigOptionsListTest extends TestCase
{
    /**
     * @var ConfigOptionsList
     */
    private $object;

    /**
     * @var MockObject|DeploymentConfig
     */
    private $deploymentConfig;

    protected function setUp(): void
    {
        $this->object = new ConfigOptionsList();
        $this->deploymentConfig = $this->createMock(DeploymentConfig::class);
    }

    public function testGetOptions()
    {
        $options = $this->object->getOptions();
        $this->assertInternalType('array', $options);
        foreach ($options as $option) {
            $this->assertInstanceOf(AbstractConfigOption::class, $option);
        }
    }

    public function testCreateConfig()
    {
        $options = [ConfigOptionsList::INPUT_KEY_BACKEND_FRONTNAME => 'admin'];
        $actualConfig = $this->object->createConfig($options, $this->deploymentConfig);

        $expectedData = [
            [
                'file' => ConfigFilePool::APP_ENV,
                'segment' => 'backend',
                'data' => [
                    'backend' => ['frontName' => 'admin']
                ]
            ]
        ];

        $this->assertInternalType('array', $actualConfig);
        /** @var ConfigData $config */
        foreach ($actualConfig as $i => $config) {
            $this->assertInstanceOf(ConfigData::class, $config);
            $this->assertSame($expectedData[$i]['file'], $config->getFileKey());
            $this->assertSame($expectedData[$i]['data'], $config->getData());
        }
    }

    public function testValidate()
    {
        $options = [ConfigOptionsList::INPUT_KEY_BACKEND_FRONTNAME => 'admin'];
        $errors = $this->object->validate($options, $this->deploymentConfig);
        $this->assertEmpty($errors);
    }

    /**
     * @param array $options
     * @param string $expectedError
     * @dataProvider validateInvalidDataProvider
     */
    public function testValidateInvalid(array $options, $expectedError)
    {
        $errors = $this->object->validate($options, $this->deploymentConfig);
        $this->assertSame([$expectedError], $errors);
    }

    /**
     * @return array
     */
    public function validateInvalidDataProvider()
    {
        return [
            [[ConfigOptionsList::INPUT_KEY_BACKEND_FRONTNAME => '**'], "Invalid backend frontname '**'"],
            [
                [ConfigOptionsList::INPUT_KEY_BACKEND_FRONTNAME => 'invalid frontname'],
                "Invalid backend frontname 'invalid frontname'"
            ],
        ];
    }
}
