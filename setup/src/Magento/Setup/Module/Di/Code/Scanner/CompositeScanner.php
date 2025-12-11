<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Setup\Module\Di\Code\Scanner;

class CompositeScanner implements ScannerInterface
{
    /**
     * @var ScannerInterface[]
     */
    protected $_children = [];

    /**
     * Add child scanner
     *
     * @param ScannerInterface $scanner
     * @param string $type
     * @return void
     */
    public function addChild(ScannerInterface $scanner, $type)
    {
        $this->_children[$type] = $scanner;
    }

    /**
     * Scan files
     *
     * @param array $files
     * @return array
     */
    public function collectEntities(array $files)
    {
        $output = [];
        foreach ($this->_children as $type => $scanner) {
            if (!isset($files[$type]) || !is_array($files[$type])) {
                continue;
            }
            $output[$type] = array_unique($scanner->collectEntities($files[$type]));
        }
        return $output;
    }
}
