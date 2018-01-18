<?php
/**
 * Application entry point
 *
 * Example - run a particular store or website:
 * --------------------------------------------
 * require __DIR__ . '/app/bootstrap.php';
 * $params = $_SERVER;
 * $params[\Magento\Store\Model\StoreManager::PARAM_RUN_CODE] = 'website2';
 * $params[\Magento\Store\Model\StoreManager::PARAM_RUN_TYPE] = 'website';
 * $bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $params);
 * \/** @var \Magento\Framework\App\Http $app *\/
 * $app = $bootstrap->createApplication(\Magento\Framework\App\Http::class);
 * $bootstrap->run($app);
 * --------------------------------------------
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

try {
    require __DIR__ . '/app/bootstrap.php';
} catch (\Exception $e) {
    echo <<<HTML
<div style="font:12px/1.35em arial, helvetica, sans-serif;">
    <div style="margin:0 0 25px 0; border-bottom:1px solid #ccc;">
        <h3 style="margin:0;font-size:1.7em;font-weight:normal;text-transform:none;text-align:left;color:#2f2f2f;">
        Autoload error</h3>
    </div>
    <p>{$e->getMessage()}</p>
</div>
HTML;
    exit(1);
}

$bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $_SERVER);
$om = $bootstrap->getObjectManager();
/** @var \Magento\Framework\Component\ComponentRegistrar $componentRegistrar */
$componentRegistrar = $om->get(\Magento\Framework\Component\ComponentRegistrar::class);
/** @var \Magento\Framework\App\ResourceConnection $resourceConnection */
$resourceConnection = $om->get(Magento\Framework\App\ResourceConnection::class);

$adapter = $resourceConnection->getConnection();

$tableNames = $adapter->getTables();

$tableComments = [];
foreach ($tableNames as $tableName) {
    $createSQL = $adapter->getCreateTable($tableName);
    preg_match_all('/\`([\w\_]+)\`.*COMMENT\s\'([^\']+)\'/', $createSQL, $matches);
    preg_match('/COMMENT=\'([^\']+)\'/', $createSQL, $tableMatch);
    $tableCommentName = $tableMatch[1] ?? null;
    $columnComment = array_combine($matches[1], $matches[2]);
    $tableComment['name'] = $tableName;
    $tableComment['columnComments'] = $columnComment;
    $tableComment['tableComment'] = $tableCommentName;
    $tableComments[$tableName] = $tableComment;
}

foreach ( $componentRegistrar->getPaths('module') as $path ) {
    $dbSchemaPath = $path . '/etc/db_schema.xml';

    if (file_exists($dbSchemaPath)) {
        $dom = new \DOMDocument('1.0');
        $dom->loadXML(file_get_contents($dbSchemaPath));
        $tables = $dom->getElementsByTagName('table');
        /** @var DOMElement $domTable */
        foreach ($tables as $domTable) {
            $tableName = $domTable->getAttribute('name');

            if (isset($tableComments[$tableName])) {
                $domTable->setAttribute('comment', $tableComments[$tableName]['tableComment']);
                $comments = $tableComments[$tableName]['columnComments'];
                /** @var DOMElement $domColumn */
                foreach ($domTable->getElementsByTagName('column') as $domColumn) {
                    if ($domColumn->hasAttribute('xsi:type')) {
                        $columnName = $domColumn->getAttribute('name');
                        if (isset($comments[$columnName])) {
                            $domColumn->setAttribute('comment', $comments[$columnName]);
                        }
                    }
                }
            }

        }
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        file_put_contents($dbSchemaPath, $dom->saveXML());
    }
}