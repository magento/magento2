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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
            array('__wakeup', 'canEmailToFriend'),
            array(),
            '',
            false
        );
        $this->productView = $this->getMock('Magento\Catalog\Block\Product\View', array(), array(), '', false);

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
        return array(
            array(true, 'never'),
            array(false, 'once')
        );
    }
}
