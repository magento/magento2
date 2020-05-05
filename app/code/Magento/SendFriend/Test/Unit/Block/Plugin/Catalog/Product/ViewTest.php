<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SendFriend\Test\Unit\Block\Plugin\Catalog\Product;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\SendFriend\Block\Plugin\Catalog\Product\View;
use Magento\SendFriend\Model\SendFriend;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ViewTest extends TestCase
{
    /** @var View */
    protected $view;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var SendFriend|MockObject */
    protected $sendfriendModel;

    /** @var \Magento\Catalog\Block\Product\View|MockObject */
    protected $productView;

    protected function setUp(): void
    {
        $this->sendfriendModel = $this->createPartialMock(
            SendFriend::class,
            ['__wakeup', 'canEmailToFriend']
        );
        $this->productView = $this->createMock(\Magento\Catalog\Block\Product\View::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->view = $this->objectManagerHelper->getObject(
            View::class,
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
