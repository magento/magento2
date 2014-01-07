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
namespace Magento\Tools\Di\Definition;

class Compressor
{
    /**
     * @var Serializer\SerializerInterface
     */
    protected $_serializer;

    /**
     * @param Serializer\SerializerInterface $serializer
     */
    public function __construct(Serializer\SerializerInterface $serializer)
    {
        $this->_serializer = $serializer;
    }

    /**
     * Compress array definitions
     *
     * @param array $definitions
     * @return mixed
     */
    public function compress(array $definitions)
    {
        $signatureList = new Compressor\UniqueList();
        $resultDefinitions = array();
        foreach ($definitions as $className => $definition) {
            $resultDefinitions[$className] = null;
            if ($definition && count($definition)) {
                $resultDefinitions[$className] = $signatureList->getNumber($definition);
            }
        }

        $signatures = $signatureList->asArray();
        foreach ($signatures as $key => $signature) {
            $signatures[$key] = $this->_serializer->serialize($signature);
        }
        return $this->_serializer->serialize(array($signatures, $resultDefinitions));
    }
}
