<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Controller;

use Magento\Framework\Composer\ComposerInformation;
use Magento\Framework\Module\PackageInfo;
use Magento\Framework\Module\PackageInfoFactory;
use Magento\Setup\Model\UpdatePackagesCache;
use Magento\Framework\Module\FullModuleList;
use Magento\Framework\Module\ModuleList;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Setup\Model\MarketplaceManager;

/**
 * @deprecated
 *
 * @link setup/src/Magento/Setup/Test/Unit/Controller/ComponentGridTest.php
 */
class ComponentGridTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ComposerInformation|\PHPUnit_Framework_MockObject_MockObject
     */
    private $composerInformationMock;

    /**
     * @var UpdatePackagesCache|\PHPUnit_Framework_MockObject_MockObject
     */
    private $updatePackagesCacheMock;

    /**
     * @var TimezoneInterface|\PHPUnit_Framework_MockObject_MockObject
     *
     */
    private $timezoneMock;

    /**
     * @var FullModuleList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fullModuleListMock;

    /**
     * @var ModuleList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $enabledModuleListMock;

    /**
     * @var PackageInfoFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $packageInfoFactoryMock;

    /**
     * Module package info
     *
     * @var PackageInfo
     */
    private $packageInfo;

    /**
     * Controller
     *
     * @var ComponentGrid
     */
    private $controller;

    /**
     * @var MarketplaceManager
     */
    private $marketplaceManagerMock;

    /**
     * @var array
     */
    private $componentData = [];

    /**
     * @var array
     */
    private $lastSyncData = [];

    /**
     * @var array
     */
    private $convertedLastSyncDate = [];

    /**#@+
     * Canned formatted date and time to return from mock
     */
    const FORMATTED_DATE = 'Jan 15, 1980';
    const FORMATTED_TIME = '1:55:55 PM';
    /**#@-*/

    public function setUp()
    {
    }

    public function testIndexAction()
    {
    }

    public function testComponentsAction()
    {
    }

    public function testSyncAction()
    {
    }

    /**
     * Prepare the timezone mock to expect calls and return formatted date and time
     *
     * @return none
     */
    private function setupTimezoneMock()
    {
    }
}
