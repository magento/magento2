<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Migration\Acl\Db;

class Updater
{
    const WRITE_MODE = 'write';

    /**
     * Resource id reader
     *
     * @var \Magento\Tools\Migration\Acl\Db\Reader
     */
    protected $_reader;

    /**
     * Resource id writer
     *
     * @var \Magento\Tools\Migration\Acl\Db\Writer
     */
    protected $_writer;

    /**
     * Operation logger
     *
     * @var \Magento\Tools\Migration\Acl\Db\AbstractLogger
     */
    protected $_logger;

    /**
     * Migration mode
     *
     * @var string
     */
    protected $_mode;

    /**
     * @param \Magento\Tools\Migration\Acl\Db\Reader $reader
     * @param \Magento\Tools\Migration\Acl\Db\Writer $writer
     * @param \Magento\Tools\Migration\Acl\Db\AbstractLogger $logger
     * @param string $mode - if value is "preview" migration does not happen
     */
    public function __construct(
        \Magento\Tools\Migration\Acl\Db\Reader $reader,
        \Magento\Tools\Migration\Acl\Db\Writer $writer,
        \Magento\Tools\Migration\Acl\Db\AbstractLogger $logger,
        $mode
    ) {
        $this->_reader = $reader;
        $this->_writer = $writer;
        $this->_logger = $logger;
        $this->_mode = $mode;
    }

    /**
     * Migrate old keys to new
     *
     * @param array $map
     * @return void
     */
    public function migrate($map)
    {
        foreach ($this->_reader->fetchAll() as $oldKey => $count) {
            $newKey = isset($map[$oldKey]) ? $map[$oldKey] : null;
            if (in_array($oldKey, $map)) {
                $newKey = $oldKey;
                $oldKey = null;
            }
            if ($newKey && $oldKey && $this->_mode == self::WRITE_MODE) {
                $this->_writer->update($oldKey, $newKey);
            }
            $this->_logger->add($oldKey, $newKey, $count);
        }
    }
}
