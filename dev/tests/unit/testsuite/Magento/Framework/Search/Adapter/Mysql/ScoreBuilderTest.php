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
namespace Magento\Framework\Search\Adapter\Mysql;

use Magento\TestFramework\Helper\ObjectManager;

class ScoreBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testBuild()
    {
        /** @var \Magento\Framework\Search\Adapter\Mysql\ScoreBuilder $builder */
        $builder = (new ObjectManager($this))->getObject('Magento\Framework\Search\Adapter\Mysql\ScoreBuilder');

        $builder->startQuery(); // start one query

        $builder->addCondition('someCondition1', 1.1);

        $builder->startQuery(); // start two query

        $builder->addCondition('someCondition2', 1.2);
        $builder->addCondition('someCondition3', 1.3);

        $builder->startQuery(); // start three query

        $builder->addCondition('someCondition4', 1.4);
        $builder->addCondition('someCondition5', 1.5);

        $builder->endQuery(10.1); // end three query

        $builder->startQuery(); // start four query

        $builder->addCondition('someCondition6', 1.6);
        $builder->addCondition('someCondition7', 1.7);

        $builder->endQuery(10.2); // end four query
        $builder->endQuery(10.3); // start two query
        $builder->endQuery(10.4); // start one query

        $result = $builder->build();

        $expected = '((someCondition1 * 1.1 + (someCondition2 * 1.2 + someCondition3 * 1.3 + ' .
            '(someCondition4 * 1.4 + someCondition5 * 1.5) * 10.1 + (someCondition6 * 1.6 + ' .
            'someCondition7 * 1.7) * 10.2) * 10.3) * 10.4) AS global_score';

        $this->assertEquals($expected, $result);
    }
}
