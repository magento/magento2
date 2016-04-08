<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SendFriend\Test\Unit\Block\Plugin\Catalog\Product;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class ViewTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\SendFriend\Block\Plugin\Catalog\Product\View */
    protected $view;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\SendFriend\Model\SendFriend|\PHPUnit_Framework_MockObject_MockObject */
    protected $sendfriendModel;

    /** @var \Magento\Catalog\Block\Product\View|\PHPUnit_Framework_MockObject_MockObject */
    protected $productView;

    protected function setUp()
    {
        $this->sendfriendModel = $this->getMock(
            'Magento\SendFriend\Model\SendFriend',
            ['__wakeup', 'canEmailToFriend'],
            [],
            '',
            false
        );
        $this->productView = $this->getMock('Magento\Catalog\Block\Product\View', [], [], '', false);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->view = $this->objectManagerHelper->getObject(
            'Magento\SendFriend\Block\Plugin\Catalog\Product\View',
            [
                'sendfriend' => $this->sendfriendModel
            ]
        );
    }

    /**
     * @dataProvider afterCanEmailToFriendDataSet
     * @param bool $result
     * @param string $callSendfriend
     */
    public function testAfterCanEmailToFriend($result, $callSendfriend)
    {
        $this->sendfriendModel->expects($this->$callSendfriend())->method('canEmailToFriend')
            ->will($this->returnValue(true));

        $this->assertTrue($this->view->afterCanEmailToFriend($this->productView, $result));
    }

    public function afterCanEmailToFriendDataSet()
    {
        return [
            [true, 'never'],
            [false, 'once']
        ];
    }
}
