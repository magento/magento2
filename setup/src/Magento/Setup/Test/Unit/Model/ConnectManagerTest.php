<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Model;

class ConnectManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $checkingCredentialsUrl = 'connect20.pr';

    /**
     * @var string
     */
    protected $urlPrefix = 'https://';

    private $serviceLocatorMock;

    private $curlClientMock;

    private $filesystemMock;

    private $composerInformationMock;

    private $applicationFactoryMock;

    public function setUp()
    {
        $this->serviceLocatorMock = $this->_getServiceLocatorMock();
        $this->composerInformationMock = $this->_getComposerInformationMock(
            ['getPackagesTypes', 'getInstalledMagentoPackages']
        );
        $this->curlClientMock = $this->_getCurlClientMock(['setCredentials', 'getBody', 'post']);
        $this->filesystemMock = $this->_getFilesystemMock(['getDirectoryRead', 'getDirectoryWrite']);
        $this->applicationFactoryMock = $this->_getApplicationFactoryMock(['create']);
    }

    /**
     * @covers \Magento\Setup\Model\ConnectManager::getCheckCredentialUrl
     */
    public function testGetCheckCredentialUrl()
    {
        $connectManager = $this->_getConnectManagerMock(
            ['getCredentialBaseUrl'],
            [
                $this->serviceLocatorMock,
                $this->composerInformationMock,
                $this->curlClientMock,
                $this->filesystemMock,
                $this->applicationFactoryMock
            ]
        );
        $connectManager
            ->expects($this->once())
            ->method('getCredentialBaseUrl')
            ->will($this->returnValue($this->checkingCredentialsUrl));

        $this->assertEquals(
            $this->urlPrefix . $this->checkingCredentialsUrl . '/check_credentials',
            $connectManager->getCheckCredentialUrl()
        );
    }

    /**
     * @covers \Magento\Setup\Model\ConnectManager::getCredentialBaseUrl
     */
    public function testGetCredentialBaseUrl()
    {
        $connectManager = $this->_getConnectManagerMock(
            ['getServiceLocator'],
            [
                $this->serviceLocatorMock,
                $this->composerInformationMock,
                $this->curlClientMock,
                $this->filesystemMock,
                $this->applicationFactoryMock
            ]
        );
        $this->serviceLocatorMock
            ->expects($this->once())
            ->method('get')
            ->with('config')
            ->will($this->returnValue(['connect' => ['check_credentials_url' => $this->checkingCredentialsUrl]]));
        $connectManager
            ->expects($this->once())
            ->method('getServiceLocator')
            ->will($this->returnValue($this->serviceLocatorMock));

        $this->assertEquals(
            $this->checkingCredentialsUrl,
            $connectManager->getCredentialBaseUrl()
        );
    }

    /**
     * @covers \Magento\Setup\Model\ConnectManager::getPackagesJsonUrl
     */
    public function testGetPackagesJsonUrl()
    {
        $connectManager = $this->_getConnectManagerMock(
            ['getCredentialBaseUrl'],
            [
                $this->serviceLocatorMock,
                $this->composerInformationMock,
                $this->curlClientMock,
                $this->filesystemMock,
                $this->applicationFactoryMock
            ]
        );
        $connectManager
            ->expects($this->once())
            ->method('getCredentialBaseUrl')
            ->will($this->returnValue($this->checkingCredentialsUrl));

        $this->assertEquals(
            $this->urlPrefix . $this->checkingCredentialsUrl . '/packages.json',
            $connectManager->getPackagesJsonUrl()
        );
    }

    /**
     * @covers \Magento\Setup\Model\ConnectManager::checkCredentialsAction
     */
    public function testCheckCredentialsAction()
    {
        $connectManager = $this->_getConnectManagerMock(
            [
                'getCheckCredentialUrl',
                'getCurlClient'
            ],
            [
                $this->serviceLocatorMock,
                $this->composerInformationMock,
                $this->curlClientMock,
                $this->filesystemMock,
                $this->applicationFactoryMock
            ]
        );
        $this->curlClientMock
            ->expects($this->once())
            ->method('setCredentials')
            ->with('username', 'password');
        $this->curlClientMock
            ->expects($this->once())
            ->method('getBody');
        $connectManager
            ->expects($this->exactly(3))
            ->method('getCurlClient')
            ->will($this->returnValue($this->curlClientMock));
        $connectManager
            ->expects($this->once())
            ->method('getCheckCredentialUrl');

        $connectManager->checkCredentialsAction('username', 'password');
    }

    /**
     * @covers \Magento\Setup\Model\ConnectManager::checkCredentialsAction
     */
    public function testCheckCredentialsActionWithException()
    {
        $connectManager = $this->_getConnectManagerMock(
            [
                'getCheckCredentialUrl',
                'getCurlClient'
            ],
            [
                $this->serviceLocatorMock,
                $this->composerInformationMock,
                $this->curlClientMock,
                $this->filesystemMock,
                $this->applicationFactoryMock
            ]
        );
        $this->curlClientMock
            ->expects($this->once())
            ->method('setCredentials')
            ->with('username', 'password');
        $this->curlClientMock
            ->expects($this->once())
            ->method('post')
            ->will($this->throwException(new \Exception));

        $this->curlClientMock
            ->expects($this->never())
            ->method('getBody');
        $connectManager
            ->expects($this->exactly(2))
            ->method('getCurlClient')
            ->will($this->returnValue($this->curlClientMock));
        $connectManager
            ->expects($this->once())
            ->method('getCheckCredentialUrl');

        $connectManager->checkCredentialsAction('username', 'password');
    }

    /**
     * @covers \Magento\Setup\Model\ConnectManager::getPackagesJson
     */
    public function testGetPackagesJson()
    {
        $connectManager = $this->_getConnectManagerMock(
            [
                'getPackagesJsonUrl',
                'getAuthJsonData',
                'getCurlClient'
            ],
            [
                $this->serviceLocatorMock,
                $this->composerInformationMock,
                $this->curlClientMock,
                $this->filesystemMock,
                $this->applicationFactoryMock
            ]
        );
        $this->curlClientMock
            ->expects($this->once())
            ->method('setCredentials')
            ->with('username', 'password');
        $this->curlClientMock
            ->expects($this->once())
            ->method('getBody');
        $connectManager
            ->expects($this->exactly(3))
            ->method('getCurlClient')
            ->will($this->returnValue($this->curlClientMock));
        $connectManager
            ->expects($this->once())
            ->method('getAuthJsonData')
            ->will($this->returnValue(['username' => 'username', 'password' => 'password']));
        $connectManager
            ->expects($this->once())
            ->method('getPackagesJsonUrl');

        $connectManager->getPackagesJson();
    }

    /**
     * @covers \Magento\Setup\Model\ConnectManager::getPackagesJson
     */
    public function testGetPackagesJsonWithException()
    {
        $connectManager = $this->_getConnectManagerMock(
            [
                'getPackagesJsonUrl',
                'getAuthJsonData',
                'getCurlClient'
            ],
            [
                $this->serviceLocatorMock,
                $this->composerInformationMock,
                $this->curlClientMock,
                $this->filesystemMock,
                $this->applicationFactoryMock
            ]
        );
        $this->curlClientMock
            ->expects($this->once())
            ->method('setCredentials')
            ->with('username', 'password');

        $this->curlClientMock
           ->expects($this->once())
           ->method('post')
           ->will($this->throwException(new \Exception));
        $this->curlClientMock
            ->expects($this->never())
            ->method('getBody');
        $connectManager
            ->expects($this->exactly(2))
            ->method('getCurlClient')
            ->will($this->returnValue($this->curlClientMock));
        $connectManager
            ->expects($this->once())
            ->method('getAuthJsonData')
            ->will($this->returnValue(['username' => 'username', 'password' => 'password']));
        $connectManager
            ->expects($this->once())
            ->method('getPackagesJsonUrl');

        $connectManager->getPackagesJson();
    }

    /**
     * @covers \Magento\Setup\Model\ConnectManager::syncPackagesForInstall
     */
    public function testSyncPackagesForInstall()
    {
        $connectManager = $this->_getConnectManagerMock(
            [
                'getPackagesJson',
                'getComposerInformation',
                'savePackagesForInstallToCache'
            ],
            [
                $this->serviceLocatorMock,
                $this->composerInformationMock,
                $this->curlClientMock,
                $this->filesystemMock,
                $this->applicationFactoryMock
            ]
        );
        $connectManager
            ->expects($this->once())
            ->method('getPackagesJson')
            ->will($this->returnValue(
                '{
                    "packages": {
                        "magento/testing-extension": {
                            "2.2.2" : {
                                "name": "magento/testing-extension",
                                "version": "2.2.2",
                                "version_normalized": "2.2.2.0",
                                "source": {
                                    "type": "hg",
                                    "url": "http://some.where/over/the/rainbow/",
                                    "reference": "35810817c14d"
                                },
                                "time": "2014-10-13 12:04:55",
                                "type": "magento2-module",
                                "authors": "Magento Connect"
                            }
                        },
                        "magento/sample-module-updater-wizard": {
                            "1.0.0" : {
                                "name": "magento/sample-module-updater-wizard",
                                "version": "3.0.0.0",
                                "version_normalized": "1.0.0.0",
                                "source": {
                                    "type": "hg",
                                    "url": "http://some.where/over/the/rainbow/",
                                    "reference": "35810817c14d"
                                },
                                "time": "2014-10-13 12:04:55",
                                "type": "magento2-module",
                                "authors": "Magento Connect"
                            }
                        }
                    }
                }'
            ));
        $this->composerInformationMock
            ->expects($this->once())
            ->method('getInstalledMagentoPackages')
            ->will($this->returnValue([["name" =>  "magento/testing-extension"]]));
        $this->composerInformationMock
            ->expects($this->any())
            ->method('getPackagesTypes')
            ->will($this->returnValue(['magento2-module']));
        $connectManager
            ->expects($this->exactly(2))
            ->method('getComposerInformation')
            ->will($this->returnValue($this->composerInformationMock));
        $connectManager
            ->expects($this->once())
            ->method('savePackagesForInstallToCache')
            ->will($this->returnValue(true));

        $connectManager->syncPackagesForInstall();
    }

    /**
     * @covers \Magento\Setup\Model\ConnectManager::getAuthJsonData
     */
    public function testGetAuthJsonData()
    {
        $connectManager = $this->_getConnectManagerMock(
            [
                'getAuthJson',
                'getCredentialBaseUrl'
            ],
            [
                $this->serviceLocatorMock,
                $this->composerInformationMock,
                $this->curlClientMock,
                $this->filesystemMock,
                $this->applicationFactoryMock
            ]
        );
        $connectManager
            ->expects($this->once())
            ->method('getAuthJson')
            ->will($this->returnValue(
                [
                    'http-basic' => [
                        $this->checkingCredentialsUrl =>
                            [
                                'username' => 'username',
                                'password' => 'password'
                            ]
                    ]
                ]
            ));
        $connectManager
            ->expects($this->once())
            ->method('getCredentialBaseUrl')
            ->will($this->returnValue($this->checkingCredentialsUrl));
        $connectManager->getAuthJsonData();
    }

    /**
     * @covers \Magento\Setup\Model\ConnectManager::getAuthJson
     */
    public function testGetAuthJson()
    {
        $connectManager = $this->_getConnectManagerMock(
            ['getFilesystem'],
            [
                $this->serviceLocatorMock,
                $this->composerInformationMock,
                $this->curlClientMock,
                $this->filesystemMock,
                $this->applicationFactoryMock
            ]
        );
        $directory = $this->_getDirectoryMock();
        $this->filesystemMock
            ->expects($this->once())
            ->method('getDirectoryRead')
            ->will($this->returnValue($directory));
        $directory
            ->expects($this->once())
            ->method('isExist')
            ->will($this->returnValue(true));
        $directory
            ->expects($this->once())
            ->method('isReadable')
            ->will($this->returnValue(true));
        $directory
            ->expects($this->once())
            ->method('readFile')
            ->will($this->returnValue(true));
        $connectManager
            ->expects($this->once())
            ->method('getFilesystem')
            ->will($this->returnValue($this->filesystemMock));

        $connectManager->getAuthJson();
    }

    /**
     * @covers \Magento\Setup\Model\ConnectManager::removeCredentials
     */
    public function testRemoveCredentials()
    {
        $connectManager = $this->_getConnectManagerMock(
            [
                'getCredentialBaseUrl',
                'getFilesystem',
                'getAuthJson',
                'saveAuthJson',
                'getDirectory'
            ],
            [
                $this->serviceLocatorMock,
                $this->composerInformationMock,
                $this->curlClientMock,
                $this->filesystemMock,
                $this->applicationFactoryMock
            ]
        );
        $directory = $this->_getDirectoryMock();
        $this->filesystemMock
            ->expects($this->once())
            ->method('getDirectoryRead')
            ->will($this->returnValue($directory));
        $directory
            ->expects($this->once())
            ->method('isExist')
            ->will($this->returnValue(true));
        $directory
            ->expects($this->once())
            ->method('isReadable')
            ->will($this->returnValue(true));
        $connectManager
            ->expects($this->once())
            ->method('getFilesystem')
            ->will($this->returnValue($this->filesystemMock));
        $connectManager
            ->expects($this->once())
            ->method('getAuthJson')
            ->will($this->returnValue(
                [
                    'http-basic' => [
                        $this->checkingCredentialsUrl =>
                            [
                                'username' => 'username',
                                'password' => 'password'
                            ]
                    ]
                ]
            ));
        $connectManager
            ->expects($this->once())
            ->method('getCredentialBaseUrl')
            ->will($this->returnValue($this->checkingCredentialsUrl));
        $connectManager
            ->expects($this->never())
            ->method('getDirectory')
            ->will($this->returnValue($directory));
        $directory
            ->expects($this->never())
            ->method('writeFile');

        $connectManager->removeCredentials();
    }

    /**
     * @covers \Magento\Setup\Model\ConnectManager::removeCredentials
     */
    public function testRemoveCredentialsEmptyHttpbasic()
    {
        $connectManager = $this->_getConnectManagerMock(
            [
                'getCredentialBaseUrl',
                'getFilesystem',
                'getAuthJson',
                'saveAuthJson'
            ],
            [
                $this->serviceLocatorMock,
                $this->composerInformationMock,
                $this->curlClientMock,
                $this->filesystemMock,
                $this->applicationFactoryMock
            ]
        );
        $directory = $this->_getDirectoryMock();
        $this->filesystemMock
            ->expects($this->once())
            ->method('getDirectoryRead')
            ->will($this->returnValue($directory));
        $directory
            ->expects($this->once())
            ->method('isExist')
            ->will($this->returnValue(true));
        $directory
            ->expects($this->once())
            ->method('isReadable')
            ->will($this->returnValue(true));
        $connectManager
            ->expects($this->once())
            ->method('getFilesystem')
            ->will($this->returnValue($this->filesystemMock));
        $connectManager
            ->expects($this->once())
            ->method('getAuthJson')
            ->will($this->returnValue([]));
        $connectManager
            ->expects($this->once())
            ->method('getCredentialBaseUrl')
            ->will($this->returnValue($this->checkingCredentialsUrl));
        $connectManager
            ->expects($this->never())
            ->method('getDirectory')
            ->will($this->returnValue($directory));
        $directory
            ->expects($this->never())
            ->method('writeFile');

        $connectManager->removeCredentials();
    }

    /**
     * @covers \Magento\Setup\Model\ConnectManager::saveAuthJson
     */
    public function testSaveAuthJson()
    {
        $connectManager = $this->_getConnectManagerMock(
            [
                'getCredentialBaseUrl',
                'getApplication'
            ],
            [
                $this->serviceLocatorMock,
                $this->composerInformationMock,
                $this->curlClientMock,
                $this->filesystemMock,
                $this->applicationFactoryMock
            ]
        );
        $application = $this->_getApplicationMock(['runComposerCommand']);
        $connectManager
            ->expects($this->once())
            ->method('getApplication')
            ->will($this->returnValue($application));
        $this->applicationFactoryMock
            ->expects($this->never())
            ->method('runComposerCommand');

        $connectManager->saveAuthJson('username', 'password');
    }

    /**
     * @covers \Magento\Setup\Model\ConnectManager::savePackagesForInstallToCache
     */
    public function testSavePackagesForInstallToCache()
    {
        $connectManager = $this->_getConnectManagerMock(
            ['getDirectory'],
            [
                $this->serviceLocatorMock,
                $this->composerInformationMock,
                $this->curlClientMock,
                $this->filesystemMock,
                $this->applicationFactoryMock
            ]
        );
        $directory = $this->_getDirectoryMock();
        $connectManager
            ->expects($this->any())
            ->method('getDirectory')
            ->will($this->returnValue($directory));
        $directory
            ->expects($this->any())
            ->method('writeFile')
            ->will($this->returnValue($directory));

        $connectManager->savePackagesForInstallToCache([]);
    }

    /**
     * @covers \Magento\Setup\Model\ConnectManager::getPackagesForInstall
     */
    public function testGetPackagesForInstallEmptyData()
    {
        $connectManager = $this->_getConnectManagerMock(
            [
                'loadPackagesForInstallFromCache',
                'getComposerInformation',

            ],
            [
                $this->serviceLocatorMock,
                $this->composerInformationMock,
                $this->curlClientMock,
                $this->filesystemMock,
                $this->applicationFactoryMock
            ]
        );
        $connectManager
            ->expects($this->once())
            ->method('loadPackagesForInstallFromCache')
            ->will($this->returnValue(false));


        $this->assertFalse($connectManager->getPackagesForInstall());
    }

    /**
     * @covers \Magento\Setup\Model\ConnectManager::getPackagesForInstall
     */
    public function testGetPackagesForInstall()
    {
        $connectManager = $this->_getConnectManagerMock(
            [
                'loadPackagesForInstallFromCache',
                'getComposerInformation',

            ],
            [
                $this->serviceLocatorMock,
                $this->composerInformationMock,
                $this->curlClientMock,
                $this->filesystemMock,
                $this->applicationFactoryMock
            ]
        );
        $connectManager
            ->expects($this->once())
            ->method('loadPackagesForInstallFromCache')
            ->will($this->returnValue([
                'packages'=> [
                    ['name' => 'test1', "type" => "magento2-module"],
                    ['name' => 'test2', "type" =>  "magento2-module"]
                ]
            ]));
        $connectManager
            ->expects($this->exactly(2))
            ->method('getComposerInformation')
            ->will($this->returnValue($this->composerInformationMock));
        $this->composerInformationMock
            ->expects($this->once())
            ->method('getInstalledMagentoPackages')
            ->will($this->returnValue(['name' => 'test1']));

        $connectManager->getPackagesForInstall();
    }

    /**
     * @covers \Magento\Setup\Model\ConnectManager::loadPackagesForInstallFromCache
     */
    public function testLoadPackagesForInstallFromCache()
    {
        $connectManager = $this->_getConnectManagerMock(
            ['getDirectory'],
            [
                $this->serviceLocatorMock,
                $this->composerInformationMock,
                $this->curlClientMock,
                $this->filesystemMock,
                $this->applicationFactoryMock
            ]
        );

        $directory = $this->_getDirectoryMock();
        $connectManager
            ->expects($this->any())
            ->method('getDirectory')
            ->will($this->returnValue($directory));
        $directory
            ->expects($this->once())
            ->method('isExist')
            ->will($this->returnValue(true));
        $directory
            ->expects($this->once())
            ->method('isReadable')
            ->will($this->returnValue(true));
        $directory
            ->expects($this->once())
            ->method('readFile')
            ->will($this->returnValue(true));

        $connectManager->loadPackagesForInstallFromCache();
    }

    /**
     * Gets serviceLocator mock
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\Zend\ServiceManager\ServiceLocatorInterface
     */
    protected function _getServiceLocatorMock()
    {
        return $this->getMockForAbstractClass('Zend\ServiceManager\ServiceLocatorInterface');
    }

    /**
     * Gets composerInformation mock
     *
     * @param null $methods
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Composer\ComposerInformation
     */
    protected function _getComposerInformationMock($methods = null)
    {
        return $this->getMock('Magento\Framework\Composer\ComposerInformation', $methods, [], '', false);
    }

    /**
     * Gets curlClient mock
     *
     * @param null $methods
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\HTTP\Client\Curl
     */
    protected function _getCurlClientMock($methods = null)
    {
        return $this->getMock('Magento\Framework\HTTP\Client\Curl', $methods, [], '', false);
    }

    /**
     * Gets Filesystem mock
     *
     * @param null $methods
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Filesystem
     */
    protected function _getFilesystemMock($methods = null)
    {
        return $this->getMock('Magento\Framework\Filesystem', $methods, [], '', false);
    }

    /**
     * Gets Directory mock
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\\Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected function _getDirectoryMock()
    {
        return $this->getMockForAbstractClass('\Magento\Framework\Filesystem\Directory\WriteInterface');
    }

    /**
     * Gets ApplicationFactory mock
     *
     * @param null $methods
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Composer\MagentoComposerApplicationFactory
     */
    protected function _getApplicationFactoryMock($methods = null)
    {
        return $this->getMock('Magento\Framework\Composer\MagentoComposerApplicationFactory', $methods, [], '', false);
    }

    /**
     * Gets Application mock
     *
     * @param null $methods
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Composer\MagentoComposerApplication
     */
    protected function _getApplicationMock($methods = null)
    {
        return $this->getMock('Magento\Composer\MagentoComposerApplication', $methods, [], '', false);
    }

    /**
     * Gets ConnectManager mock
     *
     * @param null $methods
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\ConnectManager
     */
    protected function _getConnectManagerMock($methods = null, $arguments = [])
    {
        return $this->getMock('Magento\Setup\Model\ConnectManager', $methods, $arguments, '', false);
    }
}
