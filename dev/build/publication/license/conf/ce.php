<?php
/**
 * Configuration file used by licence-tool.php script to prepare Magento Community Edition
 *
 * {license_notice}
 *
 * @category   build
 * @package    license
 * @copyright  {copyright}
 * @license    {license_link}
 */

$magentoOslAfl = array(
    'xml'   => 'AFL',
    'phtml' => 'AFL',
    'php'   => 'OSL',
    'css'   => 'AFL',
    'js'    => 'AFL',
);
$magentoAfl = $magentoOslAfl;
unset($magentoAfl['php']);

$phoenixOsl = array(
    'xml'   => 'Phoenix',
    'phtml' => 'Phoenix',
    'php'   => 'Phoenix',
    'css'   => 'Phoenix',
    'js'    => 'Phoenix'
);

$config = array(
    ''    => array('php' => 'OSL', '_recursive' => false),
    'app' => array('php' => 'OSL', '_recursive' => false),
    'app/code/community/Find'    => $magentoOslAfl,
    'app/code/community/Phoenix' => $phoenixOsl,
    'app/code/community/Social'  => $magentoOslAfl,
    'app/code/core'  => $magentoOslAfl,
    'app/code/local' => $magentoOslAfl,
    'app/design'     => $magentoAfl,
    'app/etc'        => array('xml' => 'AFL'),
    'dev'            => array_merge($magentoOslAfl, array('sql' => 'OSL', 'html' => 'AFL')),
    'downloader'     => $magentoOslAfl,
    'lib/flex'       => array('xml' => 'AFL', 'flex' => 'AFL'),
    'lib/Mage'       => $magentoOslAfl,
    'lib/Magento'    => $magentoOslAfl,
    'lib/Varien'     => $magentoOslAfl,
    'pub'            => $magentoOslAfl,
);

if (defined('EDITION_LICENSE')) {
    foreach ($config as $path => $settings) {
        foreach ($settings as $type => $license) {
            if ('_params' == $type) {
                continue;
            }
            if ('OSL' == $license || 'AFL' == $license) {
                $config[$path][$type] = EDITION_LICENSE;
            }
        }
    }
}

return $config;
