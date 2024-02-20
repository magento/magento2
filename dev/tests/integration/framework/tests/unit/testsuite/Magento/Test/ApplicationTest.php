<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Test;

use DomainException;
use Magento\Framework\App\Area;
use Magento\Framework\App\AreaList;
use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\ObjectManager\ConfigLoader;
use Magento\Framework\App\State;
use Magento\Framework\Autoload\ClassLoaderWrapper;
use Magento\Framework\Config\Scope;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Shell;
use Magento\TestFramework\Application;
use Magento\TestFramework\Helper\Bootstrap as TestFrameworkBootstrap;
use Magento\TestFramework\Db\Mysql;
use ReflectionClass;

/**
 * Provides tests for \Magento\TestFramework\Application.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 */
class ApplicationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test subject.
     *
     * @var Application
     */
    private $subject;

    /**
     * @var string
     */
    private $tempDir;

    /**
     * @var Shell|\PHPUnit\Framework\MockObject\MockObject
     */
    private $shell;

    /**
     * @var ClassLoaderWrapper|\PHPUnit\Framework\MockObject\MockObject
     */
    private $autoloadWrapper;

    /**
     * @var string
     */
    private $appMode;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->shell = $this->createMock(Shell::class);

        $this->autoloadWrapper = $this->getMockBuilder(ClassLoaderWrapper::class)
            ->disableOriginalConstructor()->getMock();

        $this->tempDir = '/temp/dir';
        $this->appMode = \Magento\Framework\App\State::MODE_DEVELOPER;

        $this->subject = new Application(
            $this->shell,
            $this->tempDir,
            'config.php',
            'global-config.php',
            '',
            $this->appMode,
            $this->autoloadWrapper
        );
    }

    /**
     * @covers \Magento\TestFramework\Application::getTempDir
     * @covers \Magento\TestFramework\Application::getDbInstance()
     * @covers \Magento\TestFramework\Application::getInitParams()
     */
    public function testConstructor()
    {
        $this->assertEquals($this->tempDir, $this->subject->getTempDir(), 'Temp directory is not set in Application');

        $initParams = $this->subject->getInitParams();
        $this->assertIsArray($initParams, 'Wrong initialization parameters type');
        $this->assertArrayHasKey(
            Bootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS,
            $initParams,
            'Directories are not configured'
        );
        $this->assertArrayHasKey(State::PARAM_MODE, $initParams, 'Application mode is not configured');
        $this->assertEquals(
            \Magento\Framework\App\State::MODE_DEVELOPER,
            $initParams[State::PARAM_MODE],
            'Wrong application mode configured'
        );
    }

    /**
     * Test installation and post-installation shell commands
     *
     * @param string $installConfigFilePath
     * @param string $globalConfigFilePath
     * @param string|null $postInstallSetupCommandsFilePath
     * @param array $expectedShellExecutionCalls
     * @param bool $isExceptionExpected
     * @dataProvider installDataProvider
     */
    public function testInstall(
        string $installConfigFilePath,
        string $globalConfigFilePath,
        ?string $postInstallSetupCommandsFilePath,
        array $expectedShellExecutionCalls,
        bool $isExceptionExpected = false
    ) {
        $tmpDir = sys_get_temp_dir();

        $subject = new Application(
            $this->shell,
            $tmpDir,
            $installConfigFilePath,
            $globalConfigFilePath,
            $tmpDir,
            $this->appMode,
            $this->autoloadWrapper,
            false,
            $postInstallSetupCommandsFilePath
        );

        // bypass db dump logic
        $dbMock = $this->getMockBuilder(Mysql::class)->disableOriginalConstructor()->getMock();

        $reflectionSubject = new ReflectionClass($subject);
        $dbProperty = $reflectionSubject->getProperty('_db');
        $dbProperty->setAccessible(true);
        $dbProperty->setValue($subject, $dbMock);

        $dbMock
            ->expects($this->any())
            ->method('isDbDumpExists')
            ->willReturnOnConsecutiveCalls(
                false,
                true
            );

        $withArgs = [];
        // Add expected shell execution calls
        foreach ($expectedShellExecutionCalls as $expectedShellExecutionArguments) {
            $withArgs[] = $expectedShellExecutionArguments;
        }

        if ($isExceptionExpected) {
            $this->expectException(DomainException::class);
            $this->expectExceptionMessage('"command" must be present in post install setup command arrays');
        } else {
            $withArgs[] = [
                PHP_BINARY . ' -f %s cache:disable -vvv --bootstrap=%s',
                [BP . '/bin/magento', $this->getInitParamsQuery($tmpDir)]
            ];
        }
        $this->shell
            ->method('execute')
            ->withConsecutive(...$withArgs);

        $subject->install(false);
    }

    /**
     * Data Provider for testInstall
     *
     * @return array
     */
    public function installDataProvider()
    {
        $installShellCommandExpectation = [
            PHP_BINARY . ' -f %s setup:install -vvv ' .
            '--db-host=%s --db-user=%s --db-password=%s --db-name=%s --db-prefix=%s ' .
            '--use-secure=%s --use-secure-admin=%s --magento-init-params=%s --no-interaction',
            [
                BP . '/bin/magento',
                '/tmp/mysql.sock',
                'root',
                '',
                'magento_integration_tests',
                '',
                '0',
                '0',
                $this->getInitParamsQuery(sys_get_temp_dir()),
                true
            ]
        ];

        return [
            'no post install setup command file' => [
                dirname(__FILE__) . '/_files/install-config-mysql1.php',
                dirname(__FILE__) . '/_files/config-global-1.php',
                null,
                [
                    $installShellCommandExpectation
                ]
            ],
            'valid post install setup command' => [
                dirname(__FILE__) . '/_files/install-config-mysql1.php',
                dirname(__FILE__) . '/_files/config-global-1.php',
                dirname(__FILE__) . '/_files/post-install-setup-command-config1.php',
                [
                    $installShellCommandExpectation,
                    [
                        PHP_BINARY . ' -f %s %s -vvv --no-interaction ' .
                        '--host=%s --dbname=%s --username=%s --password=%s --magento-init-params=%s',
                        [
                            BP . '/bin/magento',
                            'setup:db-schema:add-slave',
                            '/tmp/mysql.sock',
                            'magento_replica',
                            'root',
                            'secret',
                            $this->getInitParamsQuery(sys_get_temp_dir()),
                        ]
                    ]
                ]
            ],
            'post install setup command with both options and arguments' => [
                dirname(__FILE__) . '/_files/install-config-mysql1.php',
                dirname(__FILE__) . '/_files/config-global-1.php',
                dirname(__FILE__) . '/_files/post-install-setup-command-config3.php',
                [
                    $installShellCommandExpectation,
                    [
                        PHP_BINARY . ' -f %s %s -vvv --no-interaction %s %s --option1=%s -option2=%s --magento-init-params=%s', // phpcs:ignore
                        [
                            BP . '/bin/magento',
                            'fake:command',
                            'foo',
                            'bar',
                            'baz',
                            'qux',
                            $this->getInitParamsQuery(sys_get_temp_dir()),
                        ]
                    ]
                ]
            ],
            'post install setup command missing required value for "command"' => [
                dirname(__FILE__) . '/_files/install-config-mysql1.php',
                dirname(__FILE__) . '/_files/config-global-1.php',
                dirname(__FILE__) . '/_files/post-install-setup-command-config4.php',
                [
                    $installShellCommandExpectation
                ],
                true
            ],
        ];
    }

    /**
     * Test \Magento\TestFramework\Application will correctly load specified areas.
     *
     * @dataProvider partialLoadAreaDataProvider
     * @param string $areaCode
     * @return void
     */
    public function testPartialLoadArea(string $areaCode)
    {
        $configScope = $this->getMockBuilder(Scope::class)
            ->disableOriginalConstructor()
            ->getMock();
        $configScope->expects($this->once())
            ->method('setCurrentScope')
            ->with($this->identicalTo($areaCode));

        $configLoader = $this->getMockBuilder(ConfigLoader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $configLoader->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($areaCode))
            ->willReturn([]);

        $area = $this->getMockBuilder(Area::class)
            ->disableOriginalConstructor()
            ->getMock();
        $area->expects($this->once())
            ->method('load')
            ->with($this->identicalTo(Area::PART_CONFIG));

        $areaList = $this->getMockBuilder(AreaList::class)
            ->disableOriginalConstructor()
            ->getMock();
        $areaList->expects($this->once())
            ->method('getArea')
            ->with($this->identicalTo($areaCode))
            ->willReturn($area);

        /** @var ObjectManagerInterface|\PHPUnit\Framework\MockObject\MockObject $objectManager */
        $objectManager = $this->getMockBuilder(ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $objectManager->expects($this->once())
            ->method('configure')
            ->with($this->identicalTo([]));
        $objectManager->expects($this->exactly(3))
            ->method('get')
            ->willReturnOnConsecutiveCalls(
                $configScope,
                $configLoader,
                $areaList
            );

        TestFrameworkBootstrap::setObjectManager($objectManager);

        $this->subject->loadArea($areaCode);
    }

    /**
     * Provide test data for testPartialLoadArea().
     *
     * @return array
     */
    public function partialLoadAreaDataProvider()
    {
        return [
            [
                'area_code' => Area::AREA_GLOBAL,
            ],
            [
                'area_code' => Area::AREA_WEBAPI_REST,
            ],
            [
                'area_code' => Area::AREA_WEBAPI_SOAP,
            ],
            [
                'area_code' => Area::AREA_CRONTAB,
            ],
            [
                'area_code' => Area::AREA_GRAPHQL,
            ],
        ];
    }

    /**
     * Generate magento-init-params query responsible for dictating application paths to the magento command line
     *
     * @param string $dir The base application directory
     * @return string
     */
    private function getInitParamsQuery(string $dir)
    {
        return str_replace(
            '%s',
            $dir,
            'MAGE_DIRS[etc][path]=%s/etc&MAGE_DIRS[var][path]=%s/var&' .
            'MAGE_DIRS[var_export][path]=%s/var/export&MAGE_DIRS[media][path]=%s/pub/media&' .
            'MAGE_DIRS[static][path]=%s/pub/static&' .
            'MAGE_DIRS[view_preprocessed][path]=%s/var/view_preprocessed/pub/static&' .
            'MAGE_DIRS[code][path]=%s/generated/code&MAGE_DIRS[cache][path]=%s/var/cache&' .
            'MAGE_DIRS[log][path]=%s/var/log&MAGE_DIRS[session][path]=%s/var/session&' .
            'MAGE_DIRS[tmp][path]=%s/var/tmp&MAGE_DIRS[upload][path]=%s/var/upload&' .
            'MAGE_DIRS[pub][path]=%s/pub&MAGE_DIRS[import_export][path]=%s/var&MAGE_MODE=developer'
        );
    }
}
