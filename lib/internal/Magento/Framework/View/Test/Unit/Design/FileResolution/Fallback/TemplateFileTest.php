<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Test\Unit\Design\FileResolution\Fallback;

use Magento\Framework\App\State;
use Magento\Framework\View\Design\Fallback\RulePool;
use Magento\Framework\View\Design\FileResolution\Fallback\TemplateFile;
use Magento\Framework\View\Design\FileResolution\Fallback\ResolverInterface;
use Magento\Framework\View\Template\Html\MinifierInterface;
use Magento\Framework\View\Asset\ConfigInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\ConfigOptionsListConstants;

class TemplateFileTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ResolverInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resolver;

    /**
     * @var MinifierInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $minifier;

    /**
     * @var State|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $state;

    /**
     * @var TemplateFile
     */
    protected $object;

    /**
     * @var DeploymentConfig|\PHPUnit\Framework\MockObject\MockObject
     */
    private $deploymentConfigMock;

    /**
     * @var ConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $assetConfig;

    protected function setUp(): void
    {
        $this->resolver = $this->getMockForAbstractClass(ResolverInterface::class);
        $this->minifier = $this->getMockForAbstractClass(MinifierInterface::class);
        $this->state = $this->createMock(State::class);
        $this->assetConfig = $this->getMockForAbstractClass(ConfigInterface::class);
        $this->deploymentConfigMock = $this->createMock(DeploymentConfig::class);
        $this->object = new TemplateFile(
            $this->resolver,
            $this->minifier,
            $this->state,
            $this->assetConfig,
            $this->deploymentConfigMock
        );
    }

    /**
     * Cover getFile when mode is developer
     */
    public function testGetFileWhenStateDeveloper()
    {
        $this->assetConfig
            ->expects($this->once())
            ->method('isMinifyHtml')
            ->willReturn(true);

        $theme = $this->getMockForAbstractClass(\Magento\Framework\View\Design\ThemeInterface::class);
        $expected = 'some/file.ext';

        $this->state->expects($this->once())
            ->method('getMode')
            ->willReturn(State::MODE_DEVELOPER);
        $this->resolver->expects($this->once())
            ->method('resolve')
            ->with(RulePool::TYPE_TEMPLATE_FILE, 'file.ext', 'frontend', $theme, null, 'Magento_Module')
            ->willReturn($expected);

        $actual = $this->object->getFile('frontend', $theme, 'file.ext', 'Magento_Module');
        $this->assertSame($expected, $actual);
    }

    /**
     * Cover getFile when mode is default
     * @param string $mode
     * @param integer $onDemandInProduction
     * @param integer $forceMinification
     * @param string $method
     * @dataProvider getMinifiedDataProvider
     */
    public function testGetFileWhenModifiedNeeded($mode, $onDemandInProduction, $forceMinification, $method)
    {
        $this->assetConfig
            ->expects($this->once())
            ->method('isMinifyHtml')
            ->willReturn(true);

        $theme = $this->getMockForAbstractClass(\Magento\Framework\View\Design\ThemeInterface::class);
        $expected = 'some/file.ext';
        $expectedMinified = '/path/to/minified/some/file.ext';

        $this->deploymentConfigMock->expects($this->any())
            ->method('getConfigData')
            ->willReturnMap([
                [ConfigOptionsListConstants::CONFIG_PATH_SCD_ON_DEMAND_IN_PRODUCTION, $onDemandInProduction],
                [ConfigOptionsListConstants::CONFIG_PATH_FORCE_HTML_MINIFICATION, $forceMinification],
            ]);
        $this->state->expects($this->once())
            ->method('getMode')
            ->willReturn($mode);
        $this->resolver->expects($this->once())
            ->method('resolve')
            ->with(RulePool::TYPE_TEMPLATE_FILE, 'file.ext', 'frontend', $theme, null, 'Magento_Module')
            ->willReturn($expected);
        $this->minifier->expects($this->once())
            ->method($method)
            ->with($expected)
            ->willReturn($expectedMinified);

        $actual = $this->object->getFile('frontend', $theme, 'file.ext', 'Magento_Module');
        $this->assertSame($expectedMinified, $actual);
    }

    public function testGetFileIfMinificationIsDisabled()
    {
        $this->assetConfig
            ->expects($this->once())
            ->method('isMinifyHtml')
            ->willReturn(false);

        $theme = $this->getMockForAbstractClass(\Magento\Framework\View\Design\ThemeInterface::class);
        $expected = 'some/file.ext';

        $this->resolver->expects($this->once())
            ->method('resolve')
            ->with(RulePool::TYPE_TEMPLATE_FILE, 'file.ext', 'frontend', $theme, null, 'Magento_Module')
            ->willReturn($expected);

        $this->state->expects($this->never())
            ->method('getMode');

        $actual = $this->object->getFile('frontend', $theme, 'file.ext', 'Magento_Module');
        $this->assertSame($expected, $actual);
    }

    /**
     * Contain different methods by mode for HTML minification
     *
     * @return array
     */
    public function getMinifiedDataProvider()
    {
        return [
            'default with on demand' => [State::MODE_DEFAULT, 1, 1, 'getMinified'],
            'default without on demand' => [State::MODE_DEFAULT, 0, 0, 'getMinified'],
            'production with on demand' => [State::MODE_PRODUCTION, 1, 0, 'getMinified'],
            'production without on demand' => [State::MODE_PRODUCTION, 0, 0, 'getPathToMinified'],
            'production without on demand with minified' => [State::MODE_PRODUCTION, 0, 1, 'getMinified'],
        ];
    }
}
