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
namespace Magento\Cms\Model\Config\Source;

/**
 * @covers \Magento\Cms\Model\Config\Source\Page
 */
class PageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Cms\Model\Config\Source\Page
     */
    protected $this;

    /**
     * @var \Magento\Cms\Model\Resource\Page\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageCollectionFactory;

    /**
     * @var \Magento\Cms\Model\Resource\Page\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageCollection;

    protected function setUp()
    {
        $this->pageCollectionFactory = $this
            ->getMockBuilder('Magento\Cms\Model\Resource\Page\CollectionFactory')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'create'
                ]
            )
            ->getMock();
        $this->pageCollection = $this
            ->getMockBuilder('Magento\Cms\Model\Resource\Page\Collection')
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->this = $objectManager->getObject(
            'Magento\Cms\Model\Config\Source\Page',
            [
                'pageCollectionFactory' => $this->pageCollectionFactory
            ]
        );

        $reflection = new \ReflectionClass($this->this);
        $mathRandomProperty = $reflection->getProperty('_options');
        $mathRandomProperty->setAccessible(true);
        $mathRandomProperty->setValue($this->this, null);
    }

    /**
     * @covers \Magento\Cms\Model\Config\Source\Page::toOptionArray
     */
    public function testToOptionArray()
    {
        $resultOptions = ['val1' => 'val2'];

        $this->pageCollectionFactory
            ->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->pageCollection);
        $this->pageCollection
            ->expects($this->atLeastOnce())
            ->method('load')
            ->willReturnSelf();
        $this->pageCollection
            ->expects($this->atLeastOnce())
            ->method('toOptionIdArray')
            ->willReturn($resultOptions);

        $this->assertEquals($resultOptions, $this->this->toOptionArray());
    }
}
