<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Setup;

/**
 * Persist json files.
 */
class JsonPersistor
{
    /**
     * Persist data to json file.
     *
     * @param array $data
     * @param string $path
     * @return bool
     */
    public function persist(array $data, $path)
    {
        return file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));
    }
}
