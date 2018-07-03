<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Shell\Test\Unit;

use Magento\Framework\Shell\CommandRenderer;

class CommandRendererTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param $expectedCommand
     * @param $actualCommand
     * @param $testArguments
     * @dataProvider commandsDataProvider
     */
    public function testRender($expectedCommand, $actualCommand, $testArguments)
    {
        $commandRenderer = new CommandRenderer();
        $this->assertEquals(
            $expectedCommand,
            $commandRenderer->render($actualCommand, $testArguments)
        );
    }

    /**
     * @return array
     */
    public function commandsDataProvider()
    {
        $testArgument  = 'argument';
        $testArgument2 = 'argument2';

        $expectedCommand = "php -r %s 2>&1 | grep %s 2>&1";
        $expectedCommandArgs = "php -r '" . $testArgument . "' 2>&1 | grep '" . $testArgument2 . "' 2>&1";

        return [
            [$expectedCommand, 'php -r %s | grep %s', []],
            [$expectedCommand, 'php -r %s 2>&1 | grep %s', []],
            [$expectedCommand, 'php -r %s 2>&1 2>&1 | grep %s', []],
            [$expectedCommandArgs, 'php -r %s | grep %s', [$testArgument, $testArgument2]],
            [$expectedCommandArgs, 'php -r %s 2>&1 | grep %s', [$testArgument, $testArgument2]],
            [$expectedCommandArgs, 'php -r %s 2>&1 2>&1 | grep %s', [$testArgument, $testArgument2]],
        ];
    }
}
