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

        $output = array();
        foreach ($data as $key => $value) {
            $newKey = str_replace('config/acl/resources/', '', $key);
            $output[$newKey] = $value;
        }
        return $output;
    }
}
