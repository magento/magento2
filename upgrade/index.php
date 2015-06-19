<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
?>

<?php require_once('./views/shared/_header.phtml') ?>
<?php require_once('./views/shared/_menu.phtml') ?>

<div class="page-wrapper">
    <header class="page-header row">
        <div class="page-header-hgroup col-l-8 col-m-6">
            <div class="page-title-wrapper">
                <h1 class="page-title">Magento Setup Tool</h1>
            </div>
        </div>
        <?php require_once('./views/shared/_pageHeaderActions.phtml') ?>
    </header>
    <main id="anchor-content" class="page-content">
        <?php require_once('./views/pages/_upToDate.phtml') ?>
    </main>
</div>

<?php require_once('./views/shared/_footer.phtml') ?>
