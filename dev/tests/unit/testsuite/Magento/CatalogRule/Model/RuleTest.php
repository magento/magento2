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

namespace Magento\CatalogRule\Model;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class RuleTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\CatalogRule\Model\Rule */
    protected $rule;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Framework\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $storeManager;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $combineFactory;

    /** @var \Magento\Store\Model\Store|\PHPUnit_Framework_MockObject_MockObject */
    protected $storeModel;

    /** @var \Magento\Store\Model\Website|\PHPUnit_Framework_MockObject_MockObject */
    protected $websiteModel;

    /** @var \Magento\Rule\Model\Condition\Combine|\PHPUnit_Framework_MockObject_MockObject */
    protected $condition;

    protected function setUp()
    {
        $this->storeManager = $this->getMock('Magento\Framework\StoreManagerInterface');
        $this->storeModel = $this->getMock('Magento\Store\Model\Store', array('__wakeup', 'getId'), array(), '', false);
        $this->combineFactory = $this->getMock(
            'Magento\CatalogRule\Model\Rule\Condition\CombineFactory',
            array(
                'create'
            )
        );
        $this->productModel = $this->getMock(
            'Magento\Catalog\Model\Product',
            array(
                '__wakeup', 'getId'
            ),
            array(),
            '',
            false
        );
        $this->condition = $this->getMock(
            'Magento\Rule\Model\Condition\Combine',
            array(
                'setRule',
                'validate'
            ),
            array(),
            '',
            false
        );
        $this->websiteModel = $this->getMock(
            'Magento\Store\Model\Website',
            array(
                '__wakeup',
                'getId',
                'getDefaultStore'
            ),
            array(),
            '',
            false
        );

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->rule = $this->objectManagerHelper->getObject(
            'Magento\CatalogRule\Model\Rule',
            array(
                'storeManager' => $this->storeManager,
                'combineFactory' => $this->combineFactory
            )
        );
    }

    /**
     * @dataProvider dataProviderCallbackValidateProduct
     * @param bool $validate
     */
    public function testCallbackValidateProduct($validate)
    {
        $args['product'] = $this->productModel;
        $args['attributes'] = array();
        $args['idx'] = 0;
        $args['row'] = array(
            'entity_id' => '1',
            'entity_type_id' => '4',
            'attribute_set_id' => '4',
            'type_id' => 'simple',
            'sku' => 'Product',
            'has_options' => '0',
            'required_options' => '0',
            'created_at' => '2014-06-25 13:14:30',
            'updated_at' => '2014-06-25 14:37:15'
        );
        $this->storeManager->expects($this->any())->method('getWebsites')->with(true)
            ->will($this->returnValue(array($this->websiteModel, $this->websiteModel)));
        $this->websiteModel->expects($this->at(0))->method('getId')
            ->will($this->returnValue('1'));
        $this->websiteModel->expects($this->at(2))->method('getId')
            ->will($this->returnValue('2'));
        $this->websiteModel->expects($this->any())->method('getDefaultStore')
            ->will($this->returnValue($this->storeModel));
        $this->storeModel->expects($this->at(0))->method('getId')
            ->will($this->returnValue('1'));
        $this->storeModel->expects($this->at(1))->method('getId')
            ->will($this->returnValue('2'));
        $this->combineFactory->expects($this->any())->method('create')
            ->will($this->returnValue($this->condition));
        $this->condition->expects($this->any())->method('validate')
            ->will($this->returnValue($validate));
        $this->condition->expects($this->any())->method('setRule')
            ->will($this->returnSelf());
        $this->productModel->expects($this->any())->method('getId')
            ->will($this->returnValue(1));

        $this->rule->callbackValidateProduct($args);

        $matchingProducts = $this->rule->getMatchingProductIds();
        foreach ($matchingProducts['1'] as $matchingRules) {
            $this->assertEquals($validate, $matchingRules);
        }
    }

    public function dataProviderCallbackValidateProduct()
    {
        return array(
            array(false),
            array(true),
        );
    }
}
