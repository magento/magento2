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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Implementation of the @magentoAppIsolation DocBlock annotation - isolation of global application objects in memory
 */
namespace Magento\TestFramework\Annotation;

class AppIsolation
{
    /**
     * Flag to prevent an excessive test case isolation if the last test has been just isolated
     *
     * @var bool
     */
    private $_hasNonIsolatedTests = true;

    /**
     * @var \Magento\TestFramework\Application
     */
    private $_application;

    /**
     * Constructor
     *
     * @param \Magento\TestFramework\Application $application
     */
    public function __construct(\Magento\TestFramework\Application $application)
    {
        $this->_application = $application;
    }

    /**
     * Isolate global application objects
     */
    protected function _isolateApp()
    {
        if ($this->_hasNonIsolatedTests) {
            $this->_application->reinitialize();
            $this->_hasNonIsolatedTests = false;
        }
    }

    /**
     * Isolate application before running test case
     */
    public function startTestSuite()
    {
        $this->_isolateApp();
    }

    /**
     * Handler for 'endTest' event
     *
     * @param \PHPUnit_Framework_TestCase $test
     * @throws \Magento\Framework\Exception
     */
    public function endTest(\PHPUnit_Framework_TestCase $test)
    {
        $this->_hasNonIsolatedTests = true;

        /* Determine an isolation from doc comment */
        $annotations = $test->getAnnotations();
        if (isset($annotations['method']['magentoAppIsolation'])) {
            $isolation = $annotations['method']['magentoAppIsolation'];
            if ($isolation !== array('enabled') && $isolation !== array('disabled')) {
                throw new \Magento\Framework\Exception(
                    'Invalid "@magentoAppIsolation" annotation, can be "enabled" or "disabled" only.'
                );
            }
            $isIsolationEnabled = $isolation === array('enabled');
        } else {
            /* Controller tests should be isolated by default */
            $isIsolationEnabled = $test instanceof \Magento\TestFramework\TestCase\AbstractController;
        }

        if ($isIsolationEnabled) {
            $this->_isolateApp();
        }
    }
}
