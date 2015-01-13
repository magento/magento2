<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sendfriend\Block\Plugin\Catalog\Product;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class ViewTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Sendfriend\Block\Plugin\Catalog\Product\View */
    protected $view;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Sendfriend\Model\Sendfriend|\PHPUnit_Framework_MockObject_MockObject */
    protected $sendfriendModel;

    /** @var \Magento\Catalog\Block\Product\View|\PHPUnit_Framework_MockObject_MockObject */
    protected $productView;

    protected function setUp()
    {
        $this->sendfriendModel = $this->getMock(
            'Magento\Sendfriend\Model\Sendfriend',
            ['__wakeup', 'canEmailToFriend'],
            [],
            '',
            false
        );
        $this->productView = $this->getMock('Magento\Catalog\Block\Product\View', [], [], '', false);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->view = $this->objectManagerHelper->getObject(
            'Magento\Sendfriend\Block\Plugin\Catalog\Product\View',
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
