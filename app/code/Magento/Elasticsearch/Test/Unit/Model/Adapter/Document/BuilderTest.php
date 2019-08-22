<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\Model\Adapter\Document;

use Magento\Elasticsearch\Model\Adapter\Document\Builder;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Unit test for Magento\Elasticsearch\Model\Adapter\Document\Builder
 */
class BuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Builder
     */
    protected $builder;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $objectManager = new ObjectManagerHelper($this);
        $this->builder = $objectManager->getObject(
            \Magento\Elasticsearch\Model\Adapter\Document\Builder::class
        );
    }

    /**
     * @return void
     */
    public function testBuildWithSimpleField()
    {
        $document = [ 'fieldName' => 'fieldValue'];
        $field = 'fieldName';
        $value = 'fieldValue';

        $this->builder->addField($field, $value);
        $result = $this->builder->build();
        $this->assertEquals($document, $result);
    }

    /**
     * @return void
     */
    public function testBuildWithTwoSimpleFields()
    {
        $document = [
            'fieldNameOne' => 'fieldValueOne',
            'fieldNameTwo' => 'fieldValueTwo'
        ];

        $fieldOne = 'fieldNameOne';
        $valueOne = 'fieldValueOne';

        $fieldTwo = 'fieldNameTwo';
        $valueTwo = 'fieldValueTwo';

        $this->builder->addField($fieldOne, $valueOne);
        $this->builder->addField($fieldTwo, $valueTwo);
        $result = $this->builder->build();
        $this->assertEquals($document, $result);
    }

    /**
     * @return void
     */
    public function testBuildWithSimpleFieldAndFieldsArray()
    {
        $document = [
            'fieldNameOne' => 'fieldValueOne',
            'fieldNameTwo' => 'changedFieldValueTwo',
            'fieldThree' => 'fieldValueThree',
            'fieldFour' => 'fieldValueFour'
        ];
        $this->builder->addField('fieldNameOne', 'fieldValueOne');
        $this->builder->addField('fieldNameTwo', 'fieldValueTwo');

        $this->builder->addFields(
            [
                'fieldThree' => 'fieldValueThree',
                'fieldNameTwo' => 'changedFieldValueTwo',
                'fieldFour' => 'fieldValueFour',
            ]
        );

        $result = $this->builder->build();
        $this->assertEquals($document, $result);
    }

    /**
     * @return void
     */
    public function testBuildWithArrayField()
    {
        $document = [
            'nameOfField' => [
                'value1','value2'
            ]
        ];
        $field = 'nameOfField';
        $values = ['value1', 'value2'];

        $this->builder->addField($field, $values);

        $result = $this->builder->build();
        $this->assertEquals($document, $result);
    }

    /**
     * @return void
     */
    public function testBuildWithEmptyArrayField()
    {
        $document = [
            'nameOfField' => []
        ];
        $field = 'nameOfField';
        $values = [];

        $this->builder->addField($field, $values);

        $result = $this->builder->build();
        $this->assertEquals($document, $result);
    }

    /**
     * @return void
     */
    public function testBuildTwoDocuments()
    {
        $documentOne = [
            'docOneFieldOne' => 'docOneValueOne',
            'docOneFieldTwo' => 'docOneValueTwo'
        ];
        $documentTwo = [
            'docTwoFieldOne' => 'docTwoValueOne',
            'docTwoFieldTwo' => 'docTwoValueTwo'
        ];

        $docOneFieldOne = 'docOneFieldOne';
        $docOneValueOne = 'docOneValueOne';
        $docOneFieldTwo = 'docOneFieldTwo';
        $docOneValueTwo = 'docOneValueTwo';

        $docTwoFieldOne = 'docTwoFieldOne';
        $docTwoValueOne = 'docTwoValueOne';
        $docTwoFieldTwo = 'docTwoFieldTwo';
        $docTwoValueTwo = 'docTwoValueTwo';

        $this->builder->addField($docOneFieldOne, $docOneValueOne);
        $this->builder->addField($docOneFieldTwo, $docOneValueTwo);
        $resultOne = $this->builder->build();

        $this->builder->addField($docTwoFieldOne, $docTwoValueOne);
        $this->builder->addField($docTwoFieldTwo, $docTwoValueTwo);
        $resultTwo = $this->builder->build();

        $this->assertEquals($documentOne, $resultOne);
        $this->assertEquals($documentTwo, $resultTwo);
    }
}
