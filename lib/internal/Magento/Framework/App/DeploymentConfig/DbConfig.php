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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\App\DeploymentConfig;

class DbConfig extends AbstractSegment
{
    /**#@+
     * Array keys for Database configuration
     */
    const KEY_HOST = 'host';
    const KEY_NAME = 'dbname';
    const KEY_USER = 'username';
    const KEY_PASS = 'password';
    const KEY_PREFIX = 'table_prefix';
    const KEY_MODEL = 'model';
    const KEY_INIT_STATEMENTS = 'initStatements';
    const KEY_ACTIVE = 'active';
    /**#@-*/

    /**
     * Segment key
     */
    const CONFIG_KEY = 'db';

    /**
     * Constructor
     *
     * @param array $data
     * @throws \InvalidArgumentException
     */
    public function __construct(array $data)
    {
        $this->data = [
            self::KEY_PREFIX => '',
            'connection' => [
                'default' => [
                    self::KEY_HOST => '',
                    self::KEY_NAME => '',
                    self::KEY_USER => '',
                    self::KEY_PASS => '',
                    self::KEY_MODEL => 'mysql4',
                    self::KEY_INIT_STATEMENTS => 'SET NAMES utf8;',
                    self::KEY_ACTIVE => '1',
                ],
            ]
        ];
        $data = $this->update($data);
        $this->checkData($data);
        parent::__construct($data);
    }

    /**
     * Validate data
     *
     * @param array $data
     * @return void
     * @throws \InvalidArgumentException
     */
    private function checkData(array $data)
    {
        $prefix = $data[self::KEY_PREFIX];
        if ($prefix != '') {
            $prefix = strtolower($prefix);
            if (!preg_match('/^[a-z]+[a-z0-9_]*$/', $prefix)) {
                throw new \InvalidArgumentException(
                    'The table prefix should contain only letters (a-z), numbers (0-9) or underscores (_); '
                    . 'the first character should be a letter.'
                );
            }
        }
        foreach ($data['connection'] as $db) {
            if (empty($db[self::KEY_NAME])) {
                throw new \InvalidArgumentException('The Database Name field cannot be empty.');
            }
            if (empty($db[self::KEY_HOST])) {
                throw new \InvalidArgumentException('The Database Host field cannot be empty.');
            }
            if (empty($db[self::KEY_USER])) {
                throw new \InvalidArgumentException('The Database User field cannot be empty.');
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return self::CONFIG_KEY;
    }

    /**
     * Retrieve connection configuration by connection name
     *
     * @param string $connectionName
     * @return array|null
     */
    public function getConnection($connectionName)
    {
        return isset($this->data['connection'][$connectionName]) ? $this->data['connection'][$connectionName] : null;
    }

    /**
     * Retrieve list of connections
     *
     * @return array
     */
    public function getConnections()
    {
        return isset($this->data['connection']) ? $this->data['connection'] : array();
    }
}
