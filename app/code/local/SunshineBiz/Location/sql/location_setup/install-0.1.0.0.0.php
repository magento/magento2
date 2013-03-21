<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

/**
 * Create table 'location_area'
 */
$table = $installer->getConnection()
	->newTable($installer->getTable('location_area'))
	->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'unsigned'  => true,
		'nullable'  => false,
		'primary'   => true,
		'identity'  => true,			
		), 'Area Id')
	->addColumn('default_name', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(
		'nullable'  => false,
		), 'Area Default Name')
	->addColumn('is_active', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(
		'default'	=> 0,
		), 'Is Area Active')
	->addColumn('parent_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'unsigned'  => true,
		'default'	=> 0,
		), 'Area Parent Id')
	->addColumn('region_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'unsigned'  => true,
		'nullable'  => false,
		'default'   => '0',
		), 'Region Id')
	 ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Creation Time')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Update Time')
    ->addIndex($installer->getIdxName('location_area', 'is_active'), 'is_active')
    ->addIndex($installer->getIdxName('location_area', 'parent_id'), 'parent_id')
    ->addIndex($installer->getIdxName('location_area', 'region_id'), 'region_id')
    ->addIndex($installer->getIdxName('location_area', array('default_name', 'parent_id'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE), 
    	array('default_name', 'parent_id'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
   	->addForeignKey($installer->getFkName('location_area', 'region_id', 'directory_country_region', 'region_id'),
		'region_id',  $installer->getTable('directory_country_region'), 'region_id',
		Varien_Db_Ddl_Table::ACTION_RESTRICT, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Location Area Table');
$installer->getConnection()->createTable($table);

/**
 * Create table 'location_area_name'
 */
$table = $installer->getConnection()
	->newTable($installer->getTable('location_area_name'))
	->addColumn('area_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Area Id')
	->addColumn('locale', Varien_Db_Ddl_Table::TYPE_TEXT, 8, array(
        'nullable'  => false,
        'primary'   => true,
        'default'   => '',
        ), 'Locale')
	->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(
        'nullable'  => false,
        'default'   => null,
        ), 'Area Name')
    ->addIndex($installer->getIdxName('location_area_name', 'area_id'), 'area_id')
	->addForeignKey($installer->getFkName('location_area_name', 'area_id', 'location_area', 'id'), 
    	'area_id',  $installer->getTable('location_area'), 'id',
    	Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
	->setComment('Location Area Name Table');
$installer->getConnection()->createTable($table);

/**
 * Create table 'location_building'
 */
$table = $installer->getConnection()
	->newTable($installer->getTable('location_building'))
	->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'unsigned'  => true,
		'nullable'  => false,
		'primary'   => true,
		'identity'  => true,			
		), 'Building Id')
	->addColumn('default_name', Varien_Db_Ddl_Table::TYPE_TEXT, 100, array(
		'nullable'  => false,
		), 'Building Default Name')
	->addColumn('default_mnemonic', Varien_Db_Ddl_Table::TYPE_TEXT, 20, array(
		), 'Building Default Mnemonic')
	->addColumn('area_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'nullable'  => false,
		), 'Building Area Id')
	->addColumn('default_address', Varien_Db_Ddl_Table::TYPE_TEXT, 200, array(
		'nullable'  => false,
		), 'Building Default Address')
	->addColumn('is_active', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(
		'default'	=> 0,
		), 'Is Building Active')
	->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Creation Time')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Update Time')
    ->addIndex($installer->getIdxName('location_building', array('default_name', 'area_id'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE), 
    	array('default_name', 'area_id'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addIndex($installer->getIdxName('location_building', 'default_name'), 'default_name')
    ->addIndex($installer->getIdxName('location_building', 'default_mnemonic'), 'default_mnemonic')
    ->addIndex($installer->getIdxName('location_building', 'area_id'), 'area_id')
    ->addIndex($installer->getIdxName('location_building', 'is_active'), 'is_active')
    ->addForeignKey($installer->getFkName('location_building', 'area_id', 'location_area', 'id'),
    		'area_id',  $installer->getTable('location_area'), 'id',
    		Varien_Db_Ddl_Table::ACTION_RESTRICT, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Location Building Table');
$installer->getConnection()->createTable($table);

/**
 * Create table 'location_building_name'
 */
$table = $installer->getConnection()
	->newTable($installer->getTable('location_building_name'))
	->addColumn('building_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'unsigned'  => true,
		'nullable'  => false,
		'primary'   => true,
		), 'Building Id')
	->addColumn('locale', Varien_Db_Ddl_Table::TYPE_TEXT, 8, array(
		'nullable'  => false,
		'primary'   => true,
		'default'   => '',
		), 'Locale')
	->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 100, array(
		), 'Building Name')
	->addColumn('mnemonic', Varien_Db_Ddl_Table::TYPE_TEXT, 20, array(
		), 'Building Mnemonic')
	->addColumn('address', Varien_Db_Ddl_Table::TYPE_TEXT, 200, array(
		), 'Building Address')
	->addIndex($installer->getIdxName('location_building_name', 'building_id'), 'building_id')
	->addForeignKey($installer->getFkName('location_building_name', 'building_id', 'location_building', 'id'),
			'building_id',  $installer->getTable('location_building'), 'id',
			Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
	->setComment('Location Building Name Table');
$installer->getConnection()->createTable($table);

$installer->getConnection()
	->addIndex($installer->getTable('directory_country_region'), 
			$installer->getIdxName('directory_country_region', array('code', 'country_id'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE), 
			array('code', 'country_id'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE);
$installer->getConnection()
	->addIndex($installer->getTable('directory_country_region'),
		$installer->getIdxName('directory_country_region', array('default_name', 'country_id'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
		array('default_name', 'country_id'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE);
    
$installer->endSetup();