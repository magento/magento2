<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Cms\Command;

use Magento\Cms\Model\Wysiwyg\Validator;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Test the command.
 *
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class WysiwygRestrictCommandTest extends TestCase
{
    /**
     * @var ReinitableConfigInterface
     */
    private $config;

    /**
     * @var WysiwygRestrictCommandFactory
     */
    private $factory;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->config = $objectManager->get(ReinitableConfigInterface::class);
        $this->factory = $objectManager->get(WysiwygRestrictCommandFactory::class);
    }

    /**
     * "Execute" method cases.
     *
     * @return array
     */
    public function getExecuteCases(): array
    {
        return [
            'yes' => ['y', true],
            'no' => ['n', false],
            'no-but-different' => ['what', false]
        ];
    }

    /**
     * Test the command.
     *
     * @param string $argument
     * @param bool $expectedFlag
     * @return void
     * @dataProvider getExecuteCases
     * @magentoConfigFixture default_store cms/wysiwyg/force_valid 0
     */
    public function testExecute(string $argument, bool $expectedFlag): void
    {
        /** @var WysiwygRestrictCommand $model */
        $model = $this->factory->create();
        $tester = new CommandTester($model);
        $tester->execute(['restrict' => $argument]);

        $this->config->reinit();
        $this->assertEquals($expectedFlag, $this->config->isSetFlag(Validator::CONFIG_PATH_THROW_EXCEPTION));
    }
}
