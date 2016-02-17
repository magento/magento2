<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Composer\MagentoComposerApplication;
use Magento\Composer\InfoCommand;

class InfoCommandTest extends PHPUnit_Framework_TestCase
{

    private $installedOutput = 'name     : 3rdp/a
descrip. : Plugin project A
keywords :
versions : * 1.0.0
type     : library
names    : 3rdp/a

requires
php >=5.4.11
3rdp/c 1.1.0';

    /**
     * @var MagentoComposerApplication|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $application;

    /**
     * @var InfoCommand
     */
    protected $infoCommand;

    protected function setUp()
    {
        $this->application = $this->getMock('Magento\Composer\MagentoComposerApplication', [], [], '', false, false);

        $this->infoCommand = new InfoCommand($this->application);
    }

    /**
     * @dataProvider getCommandOutputDataProvider
     */
    public function testRun($input, $output)
    {
        $this->application->expects($this->once())->method('runComposerCommand')->willReturn($input);
        $result = $this->infoCommand->run('3rdp/a');
        $this->assertEquals($output, $result);
    }

    public function testRunInstalled()
    {
        $this->application->expects($this->once())->method('runComposerCommand')->willReturn($this->installedOutput);
        $result = $this->infoCommand->run('3rdp/a', true);
        $this->assertEquals(
            [
                'name' => '3rdp/a',
                'descrip.' => 'Plugin project A',
                'versions' => '* 1.0.0',
                'keywords' => '',
                'type' => 'library',
                'names' => '3rdp/a',
                'current_version' => '1.0.0',
                'available_versions' => [],
                'new_versions' => []
            ],
            $result
        );
    }

    /**
     * Data provider that returns different input and output for composer info command.
     *
     * @return array
     */
    public function getCommandOutputDataProvider()
    {
        return [
            'Package not installed' => [
                'name     : 3rdp/a
descrip. : Plugin project A
keywords :
versions : 1.0.0, 1.1.0
type     : library
names    : 3rdp/a

requires
php >=5.4.11
3rdp/c 1.1.0',
                [
                    'name' => '3rdp/a',
                    'descrip.' => 'Plugin project A',
                    'versions' => '1.0.0, 1.1.0',
                    'keywords' => '',
                    'type' => 'library',
                    'names' => '3rdp/a',
                    'current_version' => '',
                    'available_versions' => [
                        '1.0.0',
                        '1.1.0'
                    ],
                    'new_versions' => [
                        '1.0.0',
                        '1.1.0'
                    ]
                ]
            ],
            'Package installed' => [
                'name     : 3rdp/a
descrip. : Plugin project A
keywords :
versions : 1.0.0, 1.1.0, * 1.1.2, 1.2.0
type     : library
names    : 3rdp/a

requires
php >=5.4.11
3rdp/c 1.1.0',
                [
                    'name' => '3rdp/a',
                    'descrip.' => 'Plugin project A',
                    'versions' => '1.0.0, 1.1.0, * 1.1.2, 1.2.0',
                    'keywords' => '',
                    'type' => 'library',
                    'names' => '3rdp/a',
                    'current_version' => '1.1.2',
                    'available_versions' => [
                        '1.0.0',
                        '1.1.0',
                        '1.2.0'
                    ],
                    'new_versions' => [
                        '1.2.0'
                    ]
                ]
            ],
        ];
    }
}
