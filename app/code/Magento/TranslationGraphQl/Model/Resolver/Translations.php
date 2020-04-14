<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TranslationGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\TranslateInterface;

/**
 * Resolver for retrieving translated phrases
 */
class Translations implements ResolverInterface
{
    /**
     * @var TranslateInterface
     */
    private $translate;

    /**
     * Translations constructor.
     * @param TranslateInterface $translate
     */
    public function __construct(
        TranslateInterface $translate
    ) {
        $this->translate = $translate;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $this->translate->setLocale($args['locale']);
        $this->translate->loadData();

        $phrases = $this->getPhrases($args);
        $translatedPhrases = $this->getTranslatedPhrases($phrases);

        return [
            'items' => $translatedPhrases,
        ];
    }

    /**
     * Get phrases to be translated
     *
     * @param array $args
     * @return string[]
     * @throws GraphQlInputException
     */
    private function getPhrases(array $args): array
    {
        if (!isset($args['phrases']) || !is_array($args['phrases']) || count($args['phrases']) === 0) {
            throw new GraphQlInputException(__('"Phrases" to be translated must be specified'));
        }

        return $args['phrases'];
    }

    /**
     * Get translated phrases
     *
     * @param array $phrases
     * @return array
     */
    private function getTranslatedPhrases(array $phrases): array
    {
        $data = $this->translate->getData();
        $translatedPrases = [];
        foreach ($phrases as $phrase) {
            $translatedPrases[$phrase] = [
                'original' => $phrase,
                'translated' => array_key_exists($phrase, $data) ? $data[$phrase] : $phrase
            ];
        }
        return $translatedPrases;
    }
}
