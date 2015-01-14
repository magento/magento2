<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
        $output = [];

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
                $config = [];
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

        return ['instance' => $instanceName, 'method' => $methodName];
    }

    /**
     * Convert schedule cron configurations
     *
     * @param \DOMElement $jobConfig
     * @return array
     */
    protected function convertCronSchedule(\DOMElement $jobConfig)
    {
        $result = [];
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
        $result = [];
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
