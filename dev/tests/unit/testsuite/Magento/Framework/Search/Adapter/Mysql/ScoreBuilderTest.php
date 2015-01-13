<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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

        $builder->startQuery();
        $builder->endQuery(1);

        $result = $builder->build();

        $expected = '((someCondition1 * 1.1 + (someCondition2 * 1.2 + someCondition3 * 1.3 + ' .
            '(someCondition4 * 1.4 + someCondition5 * 1.5) * 10.1 + (someCondition6 * 1.6 + ' .
            'someCondition7 * 1.7) * 10.2) * 10.3) * 10.4 + (0)) AS global_score';

        $this->assertEquals($expected, $result);
    }
}
