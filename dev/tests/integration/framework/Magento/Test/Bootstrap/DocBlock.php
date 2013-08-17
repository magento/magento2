<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Bootstrap of the custom DocBlock annotations
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Magento_Test_Bootstrap_DocBlock
{
    /**
     * @var string
     */
    private $_fixturesBaseDir;

    /**
     * @param string $fixturesBaseDir
     */
    public function __construct($fixturesBaseDir)
    {
        $this->_fixturesBaseDir = $fixturesBaseDir;
    }

    /**
     * Activate custom DocBlock annotations along with more-or-less permanent workarounds
     */
    public function registerAnnotations(Magento_Test_Application $application)
    {
        /*
         * Note: order of registering (and applying) annotations is important.
         * To allow config fixtures to deal with fixture stores, data fixtures should be processed first.
         */
        $eventManager = new Magento_Test_EventManager(array(
            new Magento_Test_Workaround_Segfault(),
            new Magento_Test_Workaround_Cleanup_TestCaseProperties(),
            new Magento_Test_Workaround_Cleanup_StaticProperties(),
            new Magento_Test_Isolation_WorkingDirectory(),
            new Magento_Test_Annotation_AppIsolation($application),
            new Magento_Test_Event_Transaction(new Magento_Test_EventManager(array(
                new Magento_Test_Annotation_DbIsolation(),
                new Magento_Test_Annotation_DataFixture($this->_fixturesBaseDir),
            ))),
            new Magento_Test_Annotation_AppArea($application),
            new Magento_Test_Annotation_ConfigFixture(),
        ));
        Magento_Test_Event_PhpUnit::setDefaultEventManager($eventManager);
        Magento_Test_Event_Magento::setDefaultEventManager($eventManager);
    }
}
