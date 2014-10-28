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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Install\Model\Config;

class Converter implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function convert($source)
    {
        $xpath = new \DOMXPath($source);

        $result = array(
            'steps' => array(),
            'filesystem_prerequisites' => array('writables' => array(), 'notWritables' => array())
        );

        /** @var $step DOMNode */
        foreach ($xpath->query('/install_wizard/steps/step') as $step) {
            $stepAttributes = $step->attributes;
            $id = $stepAttributes->getNamedItem('id')->nodeValue;
            $result['steps'][$id]['name'] = $id;

            $controller = $stepAttributes->getNamedItem('controller')->nodeValue;
            $result['steps'][$id]['controller'] = $controller;

            $action = $stepAttributes->getNamedItem('action')->nodeValue;
            $result['steps'][$id]['action'] = $action;

            /** @var $child DOMNode */
            foreach ($step->childNodes as $child) {
                if ($child->nodeName == 'label') {
                    $result['steps'][$id]['code'] = $child->nodeValue;
                }
            }
        }

        /** @var $step DOMNode */
        foreach ($xpath->query('/install_wizard/filesystem_prerequisites/directory') as $directory) {
            $directoryAttributes = $directory->attributes;
            $alias = $directoryAttributes->getNamedItem('alias')->nodeValue;
            $existence = $directoryAttributes->getNamedItem('existence')->nodeValue == 'true' ? '1' : '0';
            $recursive = $directoryAttributes->getNamedItem('recursive')->nodeValue == 'true' ? '1' : '0';
            if ($directoryAttributes->getNamedItem('writable')->nodeValue == 'true') {
                $result['filesystem_prerequisites']['writables'][$alias]['existence'] = $existence;
                $result['filesystem_prerequisites']['writables'][$alias]['recursive'] = $recursive;
            } else {
                $result['filesystem_prerequisites']['notwritables'][$alias]['existence'] = $existence;
                $result['filesystem_prerequisites']['notwritables'][$alias]['recursive'] = $recursive;
            }
        }

        return $result;
    }
}
