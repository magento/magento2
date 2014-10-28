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
 * Test class for \Magento\Install\Model\Wizard
 */
namespace Magento\Install\Model;

/**
 * Class WizardTest
 *
 */
class WizardTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Install\Model\Config
     */
    protected $_configMock;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilderMock;

    /**
     * @var \Magento\Install\Model\Wizard
     */
    protected $_model;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_requestMock;

    /**
     * Set up before test
     */
    public function setUp()
    {
        $this->_objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_configMock = $this->getMock('\Magento\Install\Model\Config', array(), array(), '', false);
        $this->_configMock->expects($this->any())->method('getWizardSteps')->will($this->returnValue(array()));
        $this->_urlBuilderMock = $this->getMock('\Magento\Framework\UrlInterface', array(), array(), '', false);
        $this->_requestMock = $this->getMock('\Magento\Framework\App\RequestInterface', array(), array(), '', false);
        $this->_model = $this->_objectManager->getObject(
            'Magento\Install\Model\Wizard',
            array('urlBuilder' => $this->_urlBuilderMock, 'installConfig' => $this->_configMock)
        );
    }

    /**
     * Test get step with empty request
     */
    public function testGetStepByRequest()
    {
        $this->assertFalse($this->_model->getStepByRequest($this->_requestMock));
    }
}
