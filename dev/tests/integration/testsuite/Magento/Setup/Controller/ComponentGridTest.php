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
}
