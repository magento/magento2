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
namespace Magento\Framework\Search\Request;

use Magento\TestFramework\Helper\ObjectManager;

class BinderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Search\Request\Binder
     */
    private $binder;

    protected function setUp()
    {
        $helper = new ObjectManager($this);

        $this->binder = $helper->getObject('Magento\Framework\Search\Request\Binder');
    }

    public function testBind()
    {
        $requestData = [
            'dimensions' => ['scope' => ['value' => '$sss$']],
            'queries' => ['query' => ['value' => '$query$']],
            'filters' => ['filter' => ['from' => '$from$', 'to' => '$to$', 'value' => '$filter$']],
            'from' => 0,
            'size' => 15
        ];
        $bindData = [
            'dimensions' => ['scope' => 'default'],
            'placeholder' => [
                '$query$' => 'match_query',
                '$from$' => 'filter_from',
                '$to$' => 'filter_to',
                '$filter$' => 'filter_value'
            ],
            'from' => 1,
            'size' => 10
        ];
        $expectedResult = [
            'dimensions' => ['scope' => ['value' => 'default']],
            'queries' => ['query' => ['value' => 'match_query']],
            'filters' => ['filter' => ['from' => 'filter_from', 'to' => 'filter_to', 'value' => 'filter_value']],
            'from' => 1,
            'size' => 10
        ];

        $result = $this->binder->bind($requestData, $bindData);

        $this->assertEquals($result, $expectedResult);
    }
}
