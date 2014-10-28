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

namespace Magento\Store\Block;

use Magento\TestFramework\Helper\ObjectManager;

class SwitcherTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Store\Block\Switcher */
    protected $switcher;

    /** @var \Magento\Framework\View\Element\Template\Context|\PHPUnit_Framework_MockObject_MockObject */
    protected $context;

    /** @var \Magento\Core\Helper\PostData|\PHPUnit_Framework_MockObject_MockObject */
    protected $corePostDataHelper;

    /** @var \Magento\Framework\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $storeManager;

    protected function setUp()
    {
        $this->storeManager = $this->getMockBuilder('Magento\Framework\StoreManagerInterface')->getMock();
        $this->context = $this->getMock('Magento\Framework\View\Element\Template\Context', [], [], '', false);
        $this->context->expects($this->any())->method('getStoreManager')->will($this->returnValue($this->storeManager));
        $this->corePostDataHelper = $this->getMock('Magento\Core\Helper\PostData', [], [], '', false);
        $this->switcher = (new ObjectManager($this))->getObject(
            'Magento\Store\Block\Switcher',
            [
                'context' => $this->context,
                'postDataHelper' => $this->corePostDataHelper,
            ]
        );
    }

    public function testGetTargetStorePostData()
    {
        $store = $this->getMockBuilder('Magento\Store\Model\Store')->disableOriginalConstructor()->getMock();
        $store->expects($this->any())->method('getCode')->will($this->returnValue('new-store'));
        $currentStore = $this->getMockBuilder('Magento\Store\Model\Store')->disableOriginalConstructor()->getMock();
        $currentStore->expects($this->any())->method('getCode')->will($this->returnValue('current-store'));
        $this->storeManager->expects($this->any())->method('getStore')->will($this->returnValue($currentStore));
        $this->corePostDataHelper->expects($this->any())->method('getPostData')
            ->with(null, ['___store' => 'new-store', '___from_store' => 'current-store']);

        $this->switcher->getTargetStorePostData($store);
    }


    /**
     * @dataProvider testIsStoreInUrlDataProvider
     */
    public function testIsStoreInUrl($isUseStoreInUrl)
    {
        $storeMock = $this->getMock('Magento\Store\Model\Store', array(), array(), '', false);

        $storeMock->expects($this->once())->method('isUseStoreInUrl')->will($this->returnValue($isUseStoreInUrl));

        $this->storeManager->expects($this->any())->method('getStore')->will($this->returnValue($storeMock));
        $this->assertEquals($this->switcher->isStoreInUrl(), $isUseStoreInUrl);
        // check value is cached
        $this->assertEquals($this->switcher->isStoreInUrl(), $isUseStoreInUrl);
    }

    /**
     * @see self::testIsStoreInUrlDataProvider()
     * @return array
     */
    public function testIsStoreInUrlDataProvider()
    {
        return array(array(true), array(false));
    }
}
