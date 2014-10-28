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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Cron\Model\Config\Converter;

/**
 * Converts cron parameters from XML files
 */
class Xml implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * Converting data to array type
     *
     * @param \DOMDocument $source
     * @return array
     * @throws \InvalidArgumentException
     */
    public function convert($source)
    {
        $output = array();

        if (!$source instanceof \DOMDocument) {
            return $output;
        }

        $groups = $source->getElementsByTagName('group');
        foreach ($groups as $group) {
            /** @var $group \DOMElement */
            if (!$group->hasAttribute('id')) {
                throw new \InvalidArgumentException('Attribute "id" does not exist');
            }
            /** @var \DOMElement $jobConfig */
            foreach ($group->childNodes as $jobConfig) {
                if ($jobConfig->nodeName != 'job') {
                    continue;
                }
                $jobName = $jobConfig->getAttribute('name');

                if (!$jobName) {
                    throw new \InvalidArgumentException('Attribute "name" does not exist');
                }
                $config = array();
                $config['name'] = $jobName;
                $config += $this->convertCronConfig($jobConfig);
                $config += $this->convertCronSchedule($jobConfig);
                $config += $this->convertCronConfigPath($jobConfig);

                $output[$group->getAttribute('id')][$jobName] = $config;
            }
        }
        return $output;
    }

    /**
     * Convert specific cron configurations
     *
     * @param \DOMElement $jobConfig
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function convertCronConfig(\DOMElement $jobConfig)
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

    /**
     * Convert schedule cron configurations
     *
     * @param \DOMElement $jobConfig
     * @return array
     */
    protected function convertCronSchedule(\DOMElement $jobConfig)
    {
        $result = array();
        /** @var \DOMText $schedules */
        foreach ($jobConfig->childNodes as $schedules) {
            if ($schedules->nodeName == 'schedule') {
                if (!empty($schedules->nodeValue)) {
                    $result['schedule'] = $schedules->nodeValue;
                    break;
                }
            }
            continue;
        }

        return $result;
    }

    /**
     * Convert schedule cron configurations
     *
     * @param \DOMElement $jobConfig
     * @return array
     */
    protected function convertCronConfigPath(\DOMElement $jobConfig)
    {
        $result = array();
        /** @var \DOMText $schedules */
        foreach ($jobConfig->childNodes as $schedules) {
            if ($schedules->nodeName == 'config_path') {
                if (!empty($schedules->nodeValue)) {
                    $result['config_path'] = $schedules->nodeValue;
                    break;
                }
            }
            continue;
        }

        return $result;
    }
}
