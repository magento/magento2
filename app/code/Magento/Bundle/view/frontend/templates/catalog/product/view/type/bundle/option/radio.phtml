<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

?>
<?php /* @var $block \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option\Radio */ ?>
<?php $_option = $block->getOption(); ?>
<?php $_selections  = $_option->getSelections(); ?>
<?php $_default     = $_option->getDefaultSelection(); ?>
<?php list($_defaultQty, $_canChangeQty) = $block->getDefaultValues(); ?>

<div class="field option <?= ($_option->getRequired()) ? ' required': '' ?>">
    <label class="label">
        <span><?= $block->escapeHtml($_option->getTitle()) ?></span>
    </label>
    <div class="control">
        <div class="nested options-list">
            <?php if ($block->showSingle()): ?>
                <?= /* @escapeNotVerified */ $block->getSelectionTitlePrice($_selections[0]) ?>
                <input type="hidden"
                    class="bundle-option-<?= (int)$_option->getId() ?>  product bundle option"
                    name="bundle_option[<?= (int)$_option->getId() ?>]"
                    value="<?= (int)$_selections[0]->getSelectionId() ?>"
                    id="bundle-option-<?= (int)$_option->getId() ?>-<?= (int)$_selections[0]->getSelectionId() ?>"
                    checked="checked"
                />
            <?php else:?>
                <?php if (!$_option->getRequired()): ?>
                    <div class="field choice">
                        <input type="radio"
                               class="radio product bundle option"
                               id="bundle-option-<?= /* @escapeNotVerified */ $_option->getId() ?>"
                               name="bundle_option[<?= /* @escapeNotVerified */ $_option->getId() ?>]"
                               data-selector="bundle_option[<?= /* @escapeNotVerified */ $_option->getId() ?>]"
                               <?= ($_default && $_default->isSalable())?'':' checked="checked" ' ?>
                               value=""/>
                        <label class="label" for="bundle-option-<?= /* @escapeNotVerified */ $_option->getId() ?>">
                            <span><?= /* @escapeNotVerified */ __('None') ?></span>
                        </label>
                    </div>
                <?php endif; ?>
                <?php foreach ($_selections as $_selection): ?>
                    <div class="field choice">
                        <input type="radio"
                               class="radio product bundle option change-container-classname"
                               id="bundle-option-<?= /* @escapeNotVerified */ $_option->getId() ?>-<?= /* @escapeNotVerified */ $_selection->getSelectionId() ?>"
                               <?php if ($_option->getRequired()) echo 'data-validate="{\'validate-one-required-by-name\':true}"'?>
                               name="bundle_option[<?= /* @escapeNotVerified */ $_option->getId() ?>]"
                               data-selector="bundle_option[<?= /* @escapeNotVerified */ $_option->getId() ?>]"
                               <?php if ($block->isSelected($_selection)) echo ' checked="checked"' ?>
                               <?php if (!$_selection->isSaleable()) echo ' disabled="disabled"' ?>
                               value="<?= /* @escapeNotVerified */ $_selection->getSelectionId() ?>"/>
                        <label class="label"
                               for="bundle-option-<?= /* @escapeNotVerified */ $_option->getId() ?>-<?= /* @escapeNotVerified */ $_selection->getSelectionId() ?>">
                            <span><?= /* @escapeNotVerified */ $block->getSelectionTitlePrice($_selection) ?></span>
                        </label>
                    </div>
                <?php endforeach; ?>
                <div id="bundle-option-<?= /* @escapeNotVerified */ $_option->getId() ?>-container"></div>
            <?php endif; ?>
            <div class="field qty qty-holder">
                <label class="label" for="bundle-option-<?= /* @escapeNotVerified */ $_option->getId() ?>-qty-input">
                    <span><?= /* @escapeNotVerified */ __('Quantity') ?></span>
                </label>
                <div class="control">
                    <input <?php if (!$_canChangeQty) echo ' disabled="disabled"' ?>
                           id="bundle-option-<?= /* @escapeNotVerified */ $_option->getId() ?>-qty-input"
                           class="input-text qty<?php if (!$_canChangeQty) echo ' qty-disabled' ?>"
                           type="number"
                           name="bundle_option_qty[<?= /* @escapeNotVerified */ $_option->getId() ?>]"
                           data-selector="bundle_option_qty[<?= /* @escapeNotVerified */ $_option->getId() ?>]"
                           value="<?= /* @escapeNotVerified */ $_defaultQty ?>"/>
                </div>
            </div>
        </div>
    </div>
</div>
