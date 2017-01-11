<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Test\Unit\Adapter\Mysql;

use Magento\Framework\Search\Adapter\Mysql\ScoreBuilder;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ScoreBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testBuild()
    {
        /** @var \Magento\Framework\Search\Adapter\Mysql\ScoreBuilder $builder */
        $builder = (new ObjectManager($this))->getObject(\Magento\Framework\Search\Adapter\Mysql\ScoreBuilder::class);

        $builder->startQuery(); // start one query

        $builder->addCondition('someCondition1');

        $builder->startQuery(); // start two query

        $builder->addCondition('someCondition2');
        $builder->addCondition('someCondition3');

        $builder->startQuery(); // start three query

        $builder->addCondition('someCondition4');
        $builder->addCondition('someCondition5');

        $builder->endQuery(10.1); // end three query

        $builder->startQuery(); // start four query

        $builder->addCondition('someCondition6');
        $builder->addCondition('someCondition7');

        $builder->endQuery(10.2); // end four query
        $builder->endQuery(10.3); // start two query
        $builder->endQuery(10.4); // start one query

        $builder->startQuery();
        $builder->endQuery(1);

        $result = $builder->build();

        $weightExpression = 'POW(2, ' . ScoreBuilder::WEIGHT_FIELD . ')';
        $expected = '((LEAST((someCondition1), 1000000) * %1$s + (LEAST((someCondition2), 1000000) * %1$s'
            . ' + LEAST((someCondition3), 1000000) * %1$s + '
            . '(LEAST((someCondition4), 1000000) * %1$s + LEAST((someCondition5), 1000000) * %1$s) * 10.1'
            . ' + (LEAST((someCondition6), 1000000) * %1$s + '
            . 'LEAST((someCondition7), 1000000) * %1$s) * 10.2) * 10.3) * 10.4 + (0)) AS ' . $builder->getScoreAlias();
        $expected = sprintf($expected, $weightExpression);
        $this->assertEquals($expected, $result);
    }
}
