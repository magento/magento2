<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Migration\Acl\Db;

class FileReader
{
    /**
     * Extract resource id map from provided file
     *
     * @param string $fileName
     * @return array
     * @throws \InvalidArgumentException
     */
    public function extractData($fileName)
    {
        if (empty($fileName)) {
            throw new \InvalidArgumentException('Please specify correct name of a file that contains identifier map');
        }
        if (false == file_exists($fileName)) {
            throw new \InvalidArgumentException('Provided identifier map file (' . $fileName . ') doesn\'t exist');
        }
        $data = json_decode(file_get_contents($fileName), true);

        $output = [];
        foreach ($data as $key => $value) {
            $newKey = str_replace('config/acl/resources/', '', $key);
            $output[$newKey] = $value;
        }
        return $output;
    }
}
