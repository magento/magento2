<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Model\Placeholder;

use Magento\Config\Model\Placeholder\Environment;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\ConfigOptionsListConstants;
use \PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * Class EnvironmentTest
 */
class EnvironmentTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Environment
     */
    private $model;

    /**
     * @var DeploymentConfig|Mock
     */
    private $deploymentConfigMock;

    protected function setUp()
    {
        $this->deploymentConfigMock = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new Environment(
            $this->deploymentConfigMock
        );
    }

    /**
     * @param string $path
     * @param string $scope
     * @param string $scopeId
     * @param string $expected
     * @dataProvider getGenerateDataProvider
     */
    public function testGenerate($path, $scope, $scopeId, $expected)
    {
        $this->assertSame(
            $expected,
            $this->model->generate($path, $scope, $scopeId)
        );
    }

    public function getGenerateDataProvider()
    {
        return [
            [
                'web/unsecure/base_url',
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                null,
                Environment::PREFIX . 'DEFAULT__WEB__UNSECURE__BASE_URL'
            ],
            [
                'web/unsecure/base_url',
                'web',
                'test',
                Environment::PREFIX . 'WEB__TEST__WEB__UNSECURE__BASE_URL'
            ],
            [
                'web/unsecure/base_url',
                'web',
                null,
                Environment::PREFIX . 'WEB__WEB__UNSECURE__BASE_URL'
            ],
        ];
    }

    /**
     * @param string $placeholder
     * @param bool $expected
     * @dataProvider getIsPlaceholderDataProvider
     */
    public function testIsApplicable($placeholder, $expected)
    {
        $this->assertSame(
            $expected,
            $this->model->isApplicable($placeholder)
        );
    }

    /**
     * @return array
     */
    public function getIsPlaceholderDataProvider()
    {
        return [
            [Environment::PREFIX . 'TEST', true],
            ['TEST', false],
            [Environment::PREFIX . 'TEST_test', true],
            [Environment::PREFIX . '-:A', false],
            [Environment::PREFIX . '_A', false],
            [Environment::PREFIX . 'A@#$', false]
        ];
    }

    /**
     * @param string $template
     * @param string $expected
     * @dataProvider restoreDataProvider
     */
    public function testRestore($template, $expected)
    {
        $this->assertSame(
            $expected,
            $this->model->restore($template)
        );
    }

    /**
     * @return array
     */
    public function restoreDataProvider()
    {
        return [
            [Environment::PREFIX . 'TEST__CONFIG', 'test/config'],
            [Environment::PREFIX . 'TEST__CONFIG__VALUE', 'test/config/value'],
            [Environment::PREFIX . 'TEST__CONFIG_VALUE', 'test/config_value'],
        ];
    }
}
