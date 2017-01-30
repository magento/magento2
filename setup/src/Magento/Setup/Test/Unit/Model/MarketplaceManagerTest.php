<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Model;

class MarketplaceManagerTest extends \PHPUnit_Framework_TestCase
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

    public function setUp()
    {
        $this->serviceLocatorMock = $this->_getServiceLocatorMock();
        $this->composerInformationMock = $this->_getComposerInformationMock(
            ['getPackagesTypes', 'getInstalledMagentoPackages']
        );
        $this->curlClientMock = $this->_getCurlClientMock(['setCredentials', 'getStatus', 'getBody', 'post']);
        $this->filesystemMock = $this->_getFilesystemMock(['getDirectoryRead', 'getDirectoryWrite']);
    }

    /**
     * @covers \Magento\Setup\Model\MarketplaceManager::getCheckCredentialUrl
     */
    public function testGetCheckCredentialUrl()
    {
        $marketplaceManager = $this->_getMarketplaceManagerMock(
            ['getCredentialBaseUrl'],
            [
                $this->serviceLocatorMock,
                $this->composerInformationMock,
                $this->curlClientMock,
                $this->filesystemMock
            ]
        );
        $marketplaceManager
            ->expects($this->once())
            ->method('getCredentialBaseUrl')
            ->will($this->returnValue($this->checkingCredentialsUrl));

        $this->assertEquals(
            $this->urlPrefix . $this->checkingCredentialsUrl . '/check_credentials',
            $marketplaceManager->getCheckCredentialUrl()
        );
    }

    /**
     * @covers \Magento\Setup\Model\MarketplaceManager::getCredentialBaseUrl
     */
    public function testGetCredentialBaseUrl()
    {
        $marketplaceManager = $this->_getMarketplaceManagerMock(
            ['getServiceLocator'],
            [
                $this->serviceLocatorMock,
                $this->composerInformationMock,
                $this->curlClientMock,
                $this->filesystemMock
            ]
        );
        $this->serviceLocatorMock
            ->expects($this->once())
            ->method('get')
            ->with('config')
            ->will($this->returnValue(['marketplace' => ['check_credentials_url' => $this->checkingCredentialsUrl]]));
        $marketplaceManager
            ->expects($this->once())
            ->method('getServiceLocator')
            ->will($this->returnValue($this->serviceLocatorMock));

        $this->assertEquals(
            $this->checkingCredentialsUrl,
            $marketplaceManager->getCredentialBaseUrl()
        );
    }

    /**
     * @covers \Magento\Setup\Model\MarketplaceManager::getPackagesJsonUrl
     */
    public function testGetPackagesJsonUrl()
    {
        $marketplaceManager = $this->_getMarketplaceManagerMock(
            ['getCredentialBaseUrl'],
            [
                $this->serviceLocatorMock,
                $this->composerInformationMock,
                $this->curlClientMock,
                $this->filesystemMock
            ]
        );
        $marketplaceManager
            ->expects($this->once())
            ->method('getCredentialBaseUrl')
            ->will($this->returnValue($this->checkingCredentialsUrl));

        $this->assertEquals(
            $this->urlPrefix . $this->checkingCredentialsUrl . '/packages.json',
            $marketplaceManager->getPackagesJsonUrl()
        );
    }

    /**
     * @covers \Magento\Setup\Model\MarketplaceManager::checkCredentialsAction
     */
    public function testCheckCredentialsAction()
    {
        $marketplaceManager = $this->_getMarketplaceManagerMock(
            [
                'getPackagesJsonUrl',
                'getCurlClient'
            ],
            [
                $this->serviceLocatorMock,
                $this->composerInformationMock,
                $this->curlClientMock,
                $this->filesystemMock
            ]
        );
        $this->curlClientMock
            ->expects($this->once())
            ->method('setCredentials')
            ->with('username', 'password');
        $this->curlClientMock
            ->expects($this->once())
            ->method('getStatus');
        $marketplaceManager
            ->expects($this->exactly(3))
            ->method('getCurlClient')
            ->will($this->returnValue($this->curlClientMock));
        $marketplaceManager
            ->expects($this->once())
            ->method('getPackagesJsonUrl');

        $marketplaceManager->checkCredentialsAction('username', 'password');
    }

    /**
     * @covers \Magento\Setup\Model\MarketplaceManager::checkCredentialsAction
     */
    public function testCheckCredentialsActionWithException()
    {
        $marketplaceManager = $this->_getMarketplaceManagerMock(
            [
                'getPackagesJsonUrl',
                'getCurlClient'
            ],
            [
                $this->serviceLocatorMock,
                $this->composerInformationMock,
                $this->curlClientMock,
                $this->filesystemMock
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
        $marketplaceManager
            ->expects($this->exactly(2))
            ->method('getCurlClient')
            ->will($this->returnValue($this->curlClientMock));
        $marketplaceManager
            ->expects($this->once())
            ->method('getPackagesJsonUrl');

        $marketplaceManager->checkCredentialsAction('username', 'password');
    }

    /**
     * @covers \Magento\Setup\Model\MarketplaceManager::getPackagesJson
     */
    public function testGetPackagesJson()
    {
        $marketplaceManager = $this->_getMarketplaceManagerMock(
            [
                'getPackagesJsonUrl',
                'getAuthJsonData',
                'getCurlClient'
            ],
            [
                $this->serviceLocatorMock,
                $this->composerInformationMock,
                $this->curlClientMock,
                $this->filesystemMock
            ]
        );
        $this->curlClientMock
            ->expects($this->once())
            ->method('setCredentials')
            ->with('username', 'password');
        $this->curlClientMock
            ->expects($this->once())
            ->method('getBody');
        $marketplaceManager
            ->expects($this->exactly(3))
            ->method('getCurlClient')
            ->will($this->returnValue($this->curlClientMock));
        $marketplaceManager
            ->expects($this->once())
            ->method('getAuthJsonData')
            ->will($this->returnValue(['username' => 'username', 'password' => 'password']));
        $marketplaceManager
            ->expects($this->once())
            ->method('getPackagesJsonUrl');

        $marketplaceManager->getPackagesJson();
    }

    /**
     * @covers \Magento\Setup\Model\MarketplaceManager::getPackagesJson
     */
    public function testGetPackagesJsonWithException()
    {
        $marketplaceManager = $this->_getMarketplaceManagerMock(
            [
                'getPackagesJsonUrl',
                'getAuthJsonData',
                'getCurlClient'
            ],
            [
                $this->serviceLocatorMock,
                $this->composerInformationMock,
                $this->curlClientMock,
                $this->filesystemMock
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
        $marketplaceManager
            ->expects($this->exactly(2))
            ->method('getCurlClient')
            ->will($this->returnValue($this->curlClientMock));
        $marketplaceManager
            ->expects($this->once())
            ->method('getAuthJsonData')
            ->will($this->returnValue(['username' => 'username', 'password' => 'password']));
        $marketplaceManager
            ->expects($this->once())
            ->method('getPackagesJsonUrl');

        $marketplaceManager->getPackagesJson();
    }

    /**
     * @covers \Magento\Setup\Model\MarketplaceManager::syncPackagesForInstall
     */
    public function testSyncPackagesForInstall()
    {
        $marketplaceManager = $this->_getMarketplaceManagerMock(
            [
                'getPackagesJson',
                'getComposerInformation',
                'savePackagesForInstallToCache'
            ],
            [
                $this->serviceLocatorMock,
                $this->composerInformationMock,
                $this->curlClientMock,
                $this->filesystemMock
            ]
        );
        $marketplaceManager
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
        $marketplaceManager
            ->expects($this->exactly(2))
            ->method('getComposerInformation')
            ->will($this->returnValue($this->composerInformationMock));
        $marketplaceManager
            ->expects($this->once())
            ->method('savePackagesForInstallToCache')
            ->will($this->returnValue(true));

        $marketplaceManager->syncPackagesForInstall();
    }

    /**
     * @covers \Magento\Setup\Model\MarketplaceManager::getAuthJsonData
     */
    public function testGetAuthJsonData()
    {
        $marketplaceManager = $this->_getMarketplaceManagerMock(
            [
                'getAuthJson',
                'getCredentialBaseUrl'
            ],
            [
                $this->serviceLocatorMock,
                $this->composerInformationMock,
                $this->curlClientMock,
                $this->filesystemMock
            ]
        );
        $marketplaceManager
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
        $marketplaceManager
            ->expects($this->once())
            ->method('getCredentialBaseUrl')
            ->will($this->returnValue($this->checkingCredentialsUrl));
        $marketplaceManager->getAuthJsonData();
    }

    /**
     * @covers \Magento\Setup\Model\MarketplaceManager::getAuthJson
     */
    public function testGetAuthJson()
    {
        $marketplaceManager = $this->_getMarketplaceManagerMock(
            ['getFilesystem'],
            [
                $this->serviceLocatorMock,
                $this->composerInformationMock,
                $this->curlClientMock,
                $this->filesystemMock
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
        $marketplaceManager
            ->expects($this->once())
            ->method('getFilesystem')
            ->will($this->returnValue($this->filesystemMock));

        $marketplaceManager->getAuthJson();
    }

    /**
     * @covers \Magento\Setup\Model\MarketplaceManager::removeCredentials
     */
    public function testRemoveCredentials()
    {
        $marketplaceManager = $this->_getMarketplaceManagerMock(
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
                $this->filesystemMock
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
        $marketplaceManager
            ->expects($this->once())
            ->method('getFilesystem')
            ->will($this->returnValue($this->filesystemMock));
        $marketplaceManager
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
        $marketplaceManager
            ->expects($this->once())
            ->method('getCredentialBaseUrl')
            ->will($this->returnValue($this->checkingCredentialsUrl));
        $marketplaceManager
            ->expects($this->never())
            ->method('getDirectory')
            ->will($this->returnValue($directory));
        $directory
            ->expects($this->never())
            ->method('writeFile');

        $marketplaceManager->removeCredentials();
    }

    /**
     * @covers \Magento\Setup\Model\MarketplaceManager::removeCredentials
     */
    public function testRemoveCredentialsEmptyHttpbasic()
    {
        $marketplaceManager = $this->_getMarketplaceManagerMock(
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
                $this->filesystemMock
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
        $marketplaceManager
            ->expects($this->once())
            ->method('getFilesystem')
            ->will($this->returnValue($this->filesystemMock));
        $marketplaceManager
            ->expects($this->once())
            ->method('getAuthJson')
            ->will($this->returnValue([]));
        $marketplaceManager
            ->expects($this->once())
            ->method('getCredentialBaseUrl')
            ->will($this->returnValue($this->checkingCredentialsUrl));
        $marketplaceManager
            ->expects($this->never())
            ->method('getDirectory')
            ->will($this->returnValue($directory));
        $directory
            ->expects($this->never())
            ->method('writeFile');

        $marketplaceManager->removeCredentials();
    }

    /**
     * @covers \Magento\Setup\Model\MarketplaceManager::saveAuthJson
     */
    public function testSaveAuthJson()
    {
        $marketplaceManager = $this->_getMarketplaceManagerMock(
            [
                'getDirectory',
                'getCredentialBaseUrl',
                'getApplication'
            ],
            [
                $this->serviceLocatorMock,
                $this->composerInformationMock,
                $this->curlClientMock,
                $this->filesystemMock
            ]
        );

        $directory = $this->_getDirectoryMock();
        $marketplaceManager
            ->expects($this->any())
            ->method('getDirectory')
            ->will($this->returnValue($directory));
        $directory
            ->expects($this->any())
            ->method('writeFile')
            ->will($this->returnValue($directory));

        $marketplaceManager->saveAuthJson('username', 'password');
    }

    /**
     * @covers \Magento\Setup\Model\MarketplaceManager::savePackagesForInstallToCache
     */
    public function testSavePackagesForInstallToCache()
    {
        $marketplaceManager = $this->_getMarketplaceManagerMock(
            ['getDirectory'],
            [
                $this->serviceLocatorMock,
                $this->composerInformationMock,
                $this->curlClientMock,
                $this->filesystemMock
            ]
        );
        $directory = $this->_getDirectoryMock();
        $marketplaceManager
            ->expects($this->any())
            ->method('getDirectory')
            ->will($this->returnValue($directory));
        $directory
            ->expects($this->any())
            ->method('writeFile')
            ->will($this->returnValue($directory));

        $marketplaceManager->savePackagesForInstallToCache([]);
    }

    /**
     * @covers \Magento\Setup\Model\MarketplaceManager::getPackagesForInstall
     */
    public function testGetPackagesForInstallEmptyData()
    {
        $marketplaceManager = $this->_getMarketplaceManagerMock(
            [
                'loadPackagesForInstallFromCache',
                'getComposerInformation',

            ],
            [
                $this->serviceLocatorMock,
                $this->composerInformationMock,
                $this->curlClientMock,
                $this->filesystemMock
            ]
        );
        $marketplaceManager
            ->expects($this->once())
            ->method('loadPackagesForInstallFromCache')
            ->will($this->returnValue(false));


        $this->assertFalse($marketplaceManager->getPackagesForInstall());
    }

    /**
     * @covers \Magento\Setup\Model\MarketplaceManager::getPackagesForInstall
     */
    public function testGetPackagesForInstall()
    {
        $marketplaceManager = $this->_getMarketplaceManagerMock(
            [
                'loadPackagesForInstallFromCache',
                'getComposerInformation',

            ],
            [
                $this->serviceLocatorMock,
                $this->composerInformationMock,
                $this->curlClientMock,
                $this->filesystemMock
            ]
        );
        $marketplaceManager
            ->expects($this->once())
            ->method('loadPackagesForInstallFromCache')
            ->will($this->returnValue([
                'packages'=> [
                    ['name' => 'test1', "type" => "magento2-module"],
                    ['name' => 'test2', "type" =>  "magento2-module"]
                ]
            ]));
        $marketplaceManager
            ->expects($this->exactly(2))
            ->method('getComposerInformation')
            ->will($this->returnValue($this->composerInformationMock));
        $this->composerInformationMock
            ->expects($this->once())
            ->method('getInstalledMagentoPackages')
            ->will($this->returnValue(['name' => 'test1']));

        $marketplaceManager->getPackagesForInstall();
    }

    /**
     * @covers \Magento\Setup\Model\MarketplaceManager::loadPackagesForInstallFromCache
     */
    public function testLoadPackagesForInstallFromCache()
    {
        $marketplaceManager = $this->_getMarketplaceManagerMock(
            ['getDirectory'],
            [
                $this->serviceLocatorMock,
                $this->composerInformationMock,
                $this->curlClientMock,
                $this->filesystemMock
            ]
        );

        $directory = $this->_getDirectoryMock();
        $marketplaceManager
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

        $marketplaceManager->loadPackagesForInstallFromCache();
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
     * Gets MarketplaceManager mock
     *
     * @param null $methods
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\MarketplaceManager
     */
    protected function _getMarketplaceManagerMock($methods = null, $arguments = [])
    {
        return $this->getMock('Magento\Setup\Model\MarketplaceManager', $methods, $arguments, '', false);
    }
}
