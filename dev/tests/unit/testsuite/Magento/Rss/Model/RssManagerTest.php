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

namespace Magento\Rss\Model;

use \Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class RssManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Rss\Model\RssManager
     */
    protected $rssManager;

    /**
     * @var \Magento\Framework\ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = $this->getMock('Magento\Framework\ObjectManager');

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->rssManager = $objectManagerHelper->getObject(
            'Magento\Rss\Model\RssManager',
            [
                'objectManager' => $this->objectManager,
                'dataProviders' => array(
                    'rss_feed' => 'Magento\Framework\App\Rss\DataProviderInterface',
                    'bad_rss_feed' => 'Some\Class\Not\Existent',
                )
            ]
        );
    }

    public function testGetProvider()
    {
        $dataProvider = $this->getMock('Magento\Framework\App\Rss\DataProviderInterface');
        $this->objectManager->expects($this->once())->method('get')->will($this->returnValue($dataProvider));

        $this->assertInstanceOf(
             '\Magento\Framework\App\Rss\DataProviderInterface',
             $this->rssManager->getProvider('rss_feed')
        );
    }

    public function testGetProviderFirstException()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->rssManager->getProvider('wrong_rss_feed');
    }

    public function testGetProviderSecondException()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->rssManager->getProvider('bad_rss_feed');
    }
}
