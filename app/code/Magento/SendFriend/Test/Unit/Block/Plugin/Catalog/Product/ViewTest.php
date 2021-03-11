<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SendFriend\Test\Unit\Block\Plugin\Catalog\Product;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class ViewTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\SendFriend\Block\Plugin\Catalog\Product\View */
    protected $view;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\SendFriend\Model\SendFriend|\PHPUnit\Framework\MockObject\MockObject */
    protected $sendfriendModel;

    /** @var \Magento\Catalog\Block\Product\View|\PHPUnit\Framework\MockObject\MockObject */
    protected $productView;

    protected function setUp(): void
    {
        $this->sendfriendModel = $this->createPartialMock(
            \Magento\SendFriend\Model\SendFriend::class,
            ['__wakeup', 'canEmailToFriend']
        );
        $this->productView = $this->createMock(\Magento\Catalog\Block\Product\View::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->view = $this->objectManagerHelper->getObject(
            \Magento\SendFriend\Block\Plugin\Catalog\Product\View::class,
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
            ->willReturn(true);

        $this->assertTrue($this->view->afterCanEmailToFriend($this->productView, $result));
    }

    /**
     * @return array
     */
    public function afterCanEmailToFriendDataSet()
    {
        return [
            [true, 'never'],
            [false, 'once']
        ];
    }
}
