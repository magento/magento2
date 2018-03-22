<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'type_InputType_with_invalid_attribute' => [
        '<?xml version="1.0"?><config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
         <type xsi:type ="InputType" name="someInput" summary ="summary"></type></config>',
        [
            "Element 'type', attribute 'summary': The attribute 'summary' is" .
            " not allowed.\nLine: 2\n"
        ],
    ],
    'field_with_no_type_element' => [
        '<?xml version="1.0"?><config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">         
        <field xsi:type="ScalarOutputField" name="id" type="Int">
        <argument xsi:type="ObjectArrayArgument" name="" description="test_description" 
        required="" itemType="" itemRequired = ""></argument>
        </field></config>',
        [
            "Element 'field': This element is not expected. Expected is ( type ).\nLine: 2\n"
        ],
    ],
    'missing_abstract_type_definition' => [
        '<?xml version="1.0"?><config>
         <type></type></config>',
        [
            "Element 'type': The type definition is abstract.\nLine: 2\n"
        ],
    ],
    'type_enum_with_no_required_name_attribute' => [
        '<?xml version="1.0"?><config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
         <type xsi:type ="Enum"><item name="textEnumItemName">someValue</item></type></config>',
        [
            "Element 'type': The attribute 'name' is required but missing.\n" .
             "Line: 2\n"
        ],
    ],
    'type_EnumItem_with_no_required_attribute' => [
        '<?xml version="1.0"?><config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
         <type xsi:type ="Enum" name ="text2"><item>text1</item></type></config>',
        [
            "Element 'item': The attribute 'name' is required but missing.\n" .
             "Line: 2\n"
        ],
    ],
    'type_OutputType_with_missing_interface_field_attribute' => [
        '<?xml version="1.0"?><config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
          <type xsi:type ="OutputType" name="some_Name">
          <implements copyFields="textValue"></implements>
          </type></config>',
        [
            "Element 'implements': The attribute 'interface' is required but missing.\n" .
             "Line: 3\n"
        ],
    ],
    'type_OutputType_with__no_xsi_type_attribute' => [
        '<?xml version="1.0"?><config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
          <type xsi:type ="OutputType" name="someName">
          <implements interface ="someInterface" copyFields ="someBoolean"/>
          <field name ="someConcreteFieldImplementation" resolver ="resolverPath" required ="true"/>
          </type></config>',
        [
            "Element 'field': The type definition is abstract.\n" .
            "Line: 4\n"
        ],
    ],
    'type_OutputInterface_with_missing_name_and_typeResolver_attribute' => [
        '<?xml version="1.0"?><config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
          <type xsi:type ="OutputInterface"></type></config>',
        [
            "Element 'type': The attribute 'name' is required but missing.\n" .
            "Line: 2\n"
        ],
    ],
    'type_InputType_with_missing_required_name_attribute' => [
        '<?xml version="1.0"?><config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
         <type xsi:type ="InputType"></type></config>',
        [
            "Element 'type': The attribute 'name' is required but missing.\n" .
            "Line: 2\n"
        ],
    ],
    'type_InputType_with_incompatible_name_attribute_value' => [
        '<?xml version="1.0"?><config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
         <type xsi:type ="InputType" name="ProductSort"></type></config>',
        [
            "Element 'type', attribute 'name': [facet 'pattern'] The value 'ProductSort' is not accepted by the" .
            " pattern '.*Input'.\nLine: 2\n",
             "Element 'type', attribute 'name': 'ProductSort' is not a valid value of the atomic type" .
             " 'InputTypeNameType'.\nLine: 2\n"
        ],
    ],
    'type_InputType_with_incompatible_enum_value_for_scalarInput_field' => [
        '<?xml version="1.0"?><config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
         <type xsi:type ="InputType" name="SomeInput">
         <field xsi:type="ScalarInputField" type="Decimal" name="anyName" resolver="path1" required="true"></field>
         </type></config>',
        [
            "Element 'field', attribute 'type': [facet 'enumeration'] The value 'Decimal' is not an element of the" .
            " set {'Int', 'String', 'Boolean', 'Float'}.\nLine: 3\n",
            "Element 'field', attribute 'type': 'Decimal' is not a valid value of the" .
            " atomic type 'GraphQlScalarTypesEnum'.\nLine: 3\n"
        ],
    ],
    'type_InputType_with_invalid_enum_value_for_scalarOutput_field_and_invalid_field_attribute_value' => [
        '<?xml version="1.0"?><config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
         <type xsi:type ="InputType" name="SomeInput">
         <field xsi:type="ScalarInputField" type="String" name="aName" resolver="Resolverpath2" required="true"></field>
         <field xsi:type="ScalarOutputField" type="string" name="name2" resolver="resolverPath3" required="yes"></field>
         </type></config>',
        [
            "Element 'field', attribute 'type': [facet 'enumeration'] The value 'string' is" .
            " not an element of the set {'Int', 'String', 'Boolean', 'Float'}.\nLine: 4\n",
            "Element 'field', attribute 'type': 'string' is not a valid value of the" .
            " atomic type 'GraphQlScalarTypesEnum'.\nLine: 4\n",
            "Element 'field', attribute 'required': 'yes' is not a valid value of the atomic" .
            " type 'xs:boolean'.\nLine: 4\n"
        ],
    ],
    'type_InputType_field_ScalarOutput_with_missing_name_attribute_for_ScalarArgument' => [
        '<?xml version="1.0"?><config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
         <type xsi:type ="InputType" name="SomeInput">
         <field xsi:type="ScalarInputField" type="String" name="aName" resolver="Resolverpath4" required="true"></field>
         <field xsi:type="ScalarOutputField" type="Float" name="name2" resolver="resolverPath5" required="false">
            <argument xsi:type="ScalarArgument" required="true"></argument></field>
         </type></config>',
        [
            "Element 'argument': The attribute 'type' is required but missing.\nLine: 5\n",
            "Element 'argument': The attribute 'name' is required but missing.\nLine: 5\n"
        ],
    ],
    'type_OutType_with_ScalarArrayInputField_with_argument_node_not_allowed' => [
        '<?xml version="1.0"?><config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
         <type xsi:type ="OutputType" name="SomeOutput">
         <field xsi:type="ScalarInputField" type="String" name="aName" resolver="Resolverpath4" required="true">        
            <argument xsi:type="ScalarArgument" required="true"></argument></field>
         </type></config>',
        [
            "Element 'field': Character content is not allowed, because the content type is empty.\nLine: 3\n",
            "Element 'field': Element content is not allowed, because the content type is empty.\nLine: 3\n"
        ],
    ],
    'argument_ObjectArrayArgument_with_missing_name_and_itemType' => [
        '<?xml version="1.0"?><config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
         <type xsi:type ="OutputType" name="SearchResults">
         <field xsi:type="ScalarOutputField" type="Int" name="anyName" resolver="pathToResolver">
            <argument xsi:type="ObjectArgument" type="String" name="NameIsRequired" required=""/>
            <argument xsi:type="ScalarArgument" name ="pageFont" type ="String" required="true"/>            
            <argument xsi:type="ObjectArrayArgument" required="true" itemsRequired ="1"/></field>
         </type></config>',
        [
            "Element 'argument', attribute 'required': '' is not a valid value of" .
            " the atomic type 'xs:boolean'.\nLine: 4\n",
            "Element 'argument': The attribute 'itemType' is required but missing.\nLine: 6\n",
            "Element 'argument': The attribute 'name' is required but missing.\nLine: 6\n"
        ],
    ],
    'type_OutputType_with_missing_name_attribute' => [
        '<?xml version="1.0"?><config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
          <type xsi:type ="OutputType">
          <implements interface ="someInterface"/>        
          </type></config>',
        [
            "Element 'type': The attribute 'name' is required but missing.\n" .
            "Line: 2\n"
        ],
    ],
    'argument_SortArgument_with_missing_baseType' => [
        '<?xml version="1.0"?><config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
         Testing if cdata is allowed<type xsi:type ="OutputType" name="SearchResults">
         <field xsi:type="ScalarOutputField" type="Int" name="anyName" resolver="pathToResolver">
            <argument xsi:type="ObjectArgument" type="String" name="NameIsRequired" required="true"/>
            <argument xsi:type="ScalarArgument" name ="pageFont" type ="String" required="true"/>            
            <argument xsi:type="ObjectArrayArgument"
            required="1"
            name ="objectArrayName"
            itemType=" "
            itemsRequired ="false"/>
            <argument xsi:type="SortArgument" name="Sort"/></field>
         </type></config>',
        [
            "Element 'argument': The attribute 'baseType' is required but missing.\nLine: 11\n"
        ],
    ],
    'invalid_argument_FilterArgument_with missing baseType attribute' => [
        '<?xml version="1.0"?><config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
         <type xsi:type ="OutputType" name="SearchResults">
         <field xsi:type="ScalarOutputField" type="Int" name="anyName" resolver="pathToResolver">
            <argument xsi:type="ObjectArgument" type="String" name="NameIsRequired" required="true"/>
            <argument xsi:type="ScalarArgument" name ="pageFont" type ="String" required="true"/>            
            <argument xsi:type="ObjectArrayArgument" required="true" name ="objectArrayName" 
              itemType=" " itemsRequired ="0"/>
            <argument xsi:type="SortArgument" name="Sort" baseType=""/>
            <argument xsi:type="FilterArgument" name="" Filter=""/>
            </field>
         </type></config>',
        [
            "Element 'argument', attribute 'Filter': The attribute 'Filter' is not allowed.\nLine: 9\n",
            "Element 'argument': The attribute 'baseType' is required but missing.\nLine: 9\n"
        ],
    ],
    'cdata_not_allowed_with_type_or_field_element' => [
        '<?xml version="1.0"?><config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
         <type xsi:type ="OutputType" name="SearchResults">Testing if cdata is allowed with type
         <field xsi:type="ScalarOutputField" type="Int" name="anyName" resolver="pathToResolver">
           Testing if cdata is allowed with argument<argument xsi:type="ObjectArgument" 
           type="String" name="NameIsRequired" required="true"/>
            <argument xsi:type="ScalarArgument" name ="pageFont" type ="String" required="true"/>            
            <argument xsi:type="ObjectArrayArgument" required="1" 
            name ="objectArrayName" itemType=" " itemsRequired ="false"/>
            <argument xsi:type="SortArgument" name="Sort" baseType=""/></field>
         </type></config>',
        [
            "Element 'type': Character content other than whitespace is not allowed because the" .
            " content type is 'element-only'.\nLine: 2\n",
            "Element 'field': Character content other than whitespace is not allowed because the" .
            " content type is 'element-only'.\nLine: 3\n"
        ],
    ]
];
