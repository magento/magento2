<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

require_once __DIR__ . '/../../../../../app/bootstrap.php';
$bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $_SERVER);
try {
    $objectManager = $bootstrap->getObjectManager();
    /** @var $configModel \Magento\Framework\App\Config\ReinitableConfigInterface */
    $configModel = $objectManager->get('Magento\Framework\App\Config\ReinitableConfigInterface');
    $configModel->reinit();
    $config = [];

    foreach (glob(__DIR__ . '/AliasesMap/cms_content_tables_*.php', GLOB_BRACE) as $configFile) {
        $config = array_merge($config, include $configFile);
    }

    foreach ($config as $table => $field) {
        updateFieldForTable($objectManager, $table, $field);
    }
} catch (\Exception $e) {
    echo "Make sure that you launch this script with Magento 2 configured sources. \n\n";
    echo $e->getMessage();
}

/**
 * Replace {{skin url=""}} with {{view url=""}} for given table field
 *
 * @param \Magento\Framework\ObjectManagerInterface $objectManager
 * @param string $table
 * @param string $col
 * @return void
 */
function updateFieldForTable($objectManager, $table, $col)
{
    /** @var $installer \Magento\Framework\Module\DataSetup */
    $installer = $objectManager->create('Magento\Framework\Module\DataSetup');
    $installer->startSetup();

    $table = $installer->getTable($table);
    echo '-----' . "\n";
    if ($installer->getConnection()->isTableExists($table)) {
        echo 'Table `' . $table . "` processed\n";

        $indexList = $installer->getConnection()->getIndexList($table);
        $pkField = array_shift($indexList[$installer->getConnection()->getPrimaryKeyName($table)]['fields']);
        /** @var $select \Magento\Framework\DB\Select */
        $select = $installer->getConnection()->select()->from($table, ['id' => $pkField, 'content' => $col]);
        $result = $installer->getConnection()->fetchPairs($select);

        echo 'Records count: ' . count($result) . ' in table: `' . $table . "`\n";

        $logMessages = [];
        foreach ($result as $recordId => $string) {
            $content = str_replace('{{skin', '{{view', $string, $count);
            if ($count) {
                $installer->getConnection()->update(
                    $table,
                    [$col => $content],
                    $installer->getConnection()->quoteInto($pkField . '=?', $recordId)
                );
                $logMessages['replaced'][] = 'Replaced -- Id: ' . $recordId . ' in table `' . $table . '`';
            } else {
                $logMessages['skipped'][] = 'Skipped -- Id: ' . $recordId . ' in table `' . $table . '`';
            }
        }
        if (count($result)) {
            printLog($logMessages);
        }
    } else {
        echo 'Table `' . $table . "` was not found\n";
    }
    $installer->endSetup();
    echo '-----' . "\n";
}

/**
 * Print array of messages
 *
 * @param array $logMessages
 * @return void
 */
function printLog($logMessages)
{
    foreach ($logMessages as $stringsArray) {
        echo "\n";
        echo implode("\n", $stringsArray);
        echo "\n";
    }
}
