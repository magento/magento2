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
/** @var \Magento\ToolkitFramework\Application $this */
$this->resetObjectManager();
/** Clean predefined tax rates to maintain consistency */
/** @var $collection Magento\Tax\Model\Resource\Calculation\Rate\Collection */
$collection = $this->getObjectManager()->get('Magento\Tax\Model\Resource\Calculation\Rate\Collection');

/** @var $model Magento\Tax\Model\Calculation\Rate */
$model = $this->getObjectManager()->get('Magento\Tax\Model\Calculation\Rate');

foreach ($collection->getAllIds() as $id) {
    $model->setId($id);
    $model->delete();
}
/**
 * Import tax rates with import handler
 */
$filename = realpath(__DIR__ . '/tax_rates.csv');
$file = array (
    'name' => $filename,
    'type' => 'application/vnd.ms-excel',
    'tmp_name' => $filename,
    'error' => 0,
    'size' => filesize($filename),
);
$importHandler = $this->getObjectManager()->create('Magento\TaxImportExport\Model\Rate\CsvImportHandler');
$importHandler->importFromCsvFile($file);
