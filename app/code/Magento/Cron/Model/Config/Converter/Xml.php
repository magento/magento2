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
 * obtain it through the world-wide-web, please send an e-mail
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Cron
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Cron\Model\Config\Converter;

/**
 * Converts cron parameters from XML files
 */
class Xml implements \Magento\Config\ConverterInterface
{
    /**
     * Converting data to array type
     *
     * @param mixed $source
     * @return array
     * @throws LogicException
     */
    public function convert($source)
    {
        $output = array();

        if (!$source instanceof \DOMDocument) {
            return $output;
        }

        /** @var DOMNodeList $jobs */
        $jobs = $source->getElementsByTagName('job');
        /** @var DOMElement $jobConfig */
        foreach ($jobs as $jobConfig) {
            $jobName = $jobConfig->getAttribute('name');

            if (!$jobName) {
                throw new \InvalidArgumentException('Attribute "name" does not exist');
            }
            $config['name'] = $jobName;
            $config += $this->_convertCronConfig($jobConfig);

            /** @var DOMText $schedules */
            foreach ($jobConfig->childNodes as $schedules) {
                if ($schedules->nodeName == 'schedule') {
                    if (!empty($schedules->nodeValue)) {
                        $config['schedule'] = $schedules->nodeValue;
                        break;
                    }
                }
                continue;
            }
            $output[$jobName] = $config;
        }
        return $output;
    }

    /**
     * Convert specific cron configurations
     *
     * @param DOMElement $jobConfig
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function _convertCronConfig($jobConfig)
    {
        $instanceName = $jobConfig->getAttribute('instance');
        $methodName = $jobConfig->getAttribute('method');

        if (!isset($instanceName)) {
            throw new \InvalidArgumentException('Attribute "instance" does not exist');
        }
        if (!isset($methodName)) {
            throw new \InvalidArgumentException('Attribute "method" does not exist');
        }
        return array('instance' => $instanceName, 'method' => $methodName);
    }
}
