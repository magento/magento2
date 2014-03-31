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
namespace Magento\Tools\I18n\Code\Pack\Writer\File;

/**
 * Pack writer csv
 */
class Csv extends AbstractFile
{
    /**
     * File extension
     */
    const FILE_EXTENSION = 'csv';

    /**
     * {@inheritdoc}
     */
    public function _writeFile($file, $phrases)
    {
        if (self::MODE_MERGE == $this->_mode) {
            $phrases = $this->_mergeDictionaries($file, $phrases);
        }

        $writer = $this->_factory->createDictionaryWriter($file);
        /** @var \Magento\Tools\I18n\Code\Dictionary\Phrase $phrase */
        foreach ($phrases as $phrase) {
            $phrase->setContextType(null);
            $phrase->setContextValue(null);

            $writer->write($phrase);
        }
    }

    /**
     * Merge dictionaries
     *
     * @param string $file
     * @param array $phrases
     * @return array
     */
    protected function _mergeDictionaries($file, $phrases)
    {
        if (!file_exists($file)) {
            return $phrases;
        }
        $dictionary = $this->_dictionaryLoader->load($file);

        $merged = array();
        foreach ($dictionary->getPhrases() as $phrase) {
            $merged[$phrase->getPhrase()] = $phrase;
        }
        /** @var \Magento\Tools\I18n\Code\Dictionary\Phrase $phrase */
        foreach ($phrases as $phrase) {
            $merged[$phrase->getPhrase()] = $phrase;
        }
        return $merged;
    }

    /**
     * {@inheritdoc}
     */
    protected function _getFileExtension()
    {
        return self::FILE_EXTENSION;
    }
}
