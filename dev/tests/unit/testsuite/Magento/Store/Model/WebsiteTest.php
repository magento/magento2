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
namespace Magento\Store\Model;

class WebsiteTest extends \PHPUnit_Framework_TestCase
{
    public function testIsCanDelete()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $websiteCollection = $this->getMock(
            'Magento\Store\Model\Resource\Website\Collection',
            array('getSize'),
            array(),
            '',
            false
        );
        $websiteCollection->expects($this->any())->method('getSize')->will($this->returnValue(2));

        $websiteFactory = $this->getMock(
            'Magento\Store\Model\WebsiteFactory',
            array('create', 'getCollection', '__wakeup'),
            array(),
            '',
            false
        );
        $websiteFactory->expects($this->any())->method('create')->will($this->returnValue($websiteFactory));
        $websiteFactory->expects($this->any())->method('getCollection')->will($this->returnValue($websiteCollection));

        /** @var \Magento\Store\Model\Website $websiteModel */
        $websiteModel = $objectManager->getObject(
            'Magento\Store\Model\Website',
            array('websiteFactory' => $websiteFactory)
        );
        $websiteModel->setId(2);
        $this->assertTrue($websiteModel->isCanDelete());
    }
}
