<?php
/**
 * Unit test for Magento\Backend\Model\Config\Backend\Cookie\Path
 *
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
namespace Magento\Backend\Model\Config\Backend\Cookie;

use \Magento\TestFramework\Helper\ObjectManager;
use \Magento\Framework\Session\Config\Validator\CookiePathValidator;

class PathTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject | CookiePathValidator */
    private $validatorMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Install\Model\Resource\Resource */
    private $resourceMock;

    /** @var \Magento\Backend\Model\Config\Backend\Cookie\Path */
    private $model;

    public function setUp()
    {
        $this->validatorMock = $this->getMockBuilder('Magento\Framework\Session\Config\Validator\CookiePathValidator')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceMock = $this->getMockBuilder('Magento\Install\Model\Resource\Resource')
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject('Magento\Backend\Model\Config\Backend\Cookie\Path',
            [
                'configValidator' => $this->validatorMock,
                'resource' => $this->resourceMock
            ]
        );
    }

    /**
     * Method is not publicly accessible, so it must be called through parent
     *
     * @expectedException \Magento\Framework\Model\Exception
     * @expectedExceptionMessage Invalid cookie path
     */
    public function testBeforeSaveException()
    {
        $invalidCookiePath = 'invalid path';
        $this->validatorMock->expects($this->once())
            ->method('isValid')
            ->with($invalidCookiePath)
            ->willReturn(false);

        // Must throw exception
        $this->model->setValue($invalidCookiePath)->save();
    }

    /**
     * Method is not publicly accessible, so it must be called through parent
     *
     * No assertions exist because the purpose of the test is to make sure that no
     * exception gets thrown
     */
    public function testBeforeSaveNoException()
    {
        $validCookiePath = 1;
        $this->validatorMock->expects($this->once())
            ->method('isValid')
            ->with($validCookiePath)
            ->willReturn(true);
        $this->resourceMock->expects($this->any())->method('addCommitCallback')->willReturnSelf();

        // Must not throw exception
        $this->model->setValue($validCookiePath)->save();
    }

    /**
     * Method is not publicly accessible, so it must be called through parent
     *
     * Empty string should not be sent to validator
     */
    public function testBeforeSaveEmptyString()
    {
        $validCookiePath = '';
        $this->validatorMock->expects($this->never())
            ->method('isValid');

        $this->resourceMock->expects($this->any())->method('addCommitCallback')->willReturnSelf();

        // Must not throw exception
        $this->model->setValue($validCookiePath)->save();
    }
}
