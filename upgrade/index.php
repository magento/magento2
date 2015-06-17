<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
?>

<!DOCTYPE html>
<!--[if IE 9]>
<html class="ie9" lang="en">
<![endif]--><!--[if !IE]><!-->
<html lang="en" ng-app="magentoSetup">
<!--<![endif]-->
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Magento Upgrade</title>
    <link href="./styles/css/upgrade.css" rel="stylesheet" type="text/css">
    <link
        rel="icon"
        type="image/x-icon"
        href="./images/favicons/favicon.ico"
        sizes="16x16">
    <link
        rel="icon"
        type="image/png"
        href="./images/favicons/favicon-96x96.png"
        sizes="96x96">
    <link
        rel="icon"
        type="image/png"
        href="./images/favicons/favicon-32x32.png"
        sizes="32x32">
    <link
        rel="icon"
        type="image/png"
        href="./images/favicons/favicon-16x16.png"
        sizes="16x16">
</head>
<body>
<div class="menu-wrapper">
    <a
        class="logo"
        href="/"
        data-edition="Community Edition">
        <img class="logo-img"
            src="./images/logo.svg"
            alt="Magento Admin Panel">
    </a>
    <nav
        class="admin__menu"
        role="navigation">
        <ul
            id="nav"
            role="menubar">
            <li class="item-home _current level-0">
                <a
                    class="_active"
                    href="#">
                    <span>Home</span>
                </a>
            </li>
            <li class="item-home _current level-0">
                <a
                    class="_active"
                    href="#">
                    <span>Component Manager</span>
                </a>
            </li>
            <li class="item-home _current level-0 _active">
                <a
                    class="_active"
                    href="#">
                    <span>System Upgrade</span>
                </a>
            </li>
            <li
                class="item-home _current level-0"
                id="menu-magento-backend-dashboard">
                <a
                    class="_active"
                    href="#">
                    <span>System Config</span>
                </a>
            </li>
        </ul>
    </nav>
</div>

<div class="page-wrapper">
    <header class="page-header row">
        <div class="page-header-hgroup col-l-8 col-m-6">
            <div class="page-title-wrapper">
                <h1 class="page-title">Magento Setup Tool</h1>
            </div>
        </div>
        <div class="page-header-actions col-l-4 col-m-6">
            <div class="admin-user admin__action-dropdown-wrap">
                <a
                    href="#index"
                    class="admin__action-dropdown"
                    title="My Account">
                <span class="admin__action-dropdown-text">
                    <span class="admin-user-account-text">username</span>
                </span>
                </a>
                <ul class="admin__action-dropdown-menu">
                    <li>
                        <a
                            href="#settings"
                            title="Account Setting">
                            Account Setting (<span class="admin-user-name">admin</span>)
                        </a>
                    </li>
                    <li>
                        <a
                            href="#customerView"
                            title="Customer View"
                            target="_blank"
                            class="store-front">
                            Customer View
                        </a>
                    </li>
                    <li>
                        <a
                            href="#signout"
                            class="account-signout"
                            title="Sign Out">
                            Sign Out
                        </a>
                    </li>
                </ul>
            </div>
            <div class="notifications-wrapper admin__action-dropdown-wrap">
                <a
                    class="notifications-action admin__action-dropdown"
                    href="http://magento2.local/index.php/admin/admin/notification/index/"
                    title="Notifications"></a>
            </div>
            <div class="search-global">
                <form
                    action="#"
                    id="form-search">
                    <div class="search-global-field">
                        <label class="search-global-label" for="search-global"></label>
                        <input type="hidden" name="query">
                        <div class="mage-suggest">
                            <div class="mage-suggest-inner">
                                <input
                                    type="text"
                                    class="search-global-input"
                                    id="search-global"
                                    autocomplete="off">
                                <div class="autocomplete-results"></div>
                            </div>
                        </div>
                        <button
                            type="submit"
                            class="search-global-action"
                            title="Search"></button>
                    </div>
                </form>
            </div>
        </div>
    </header>
    <main
        id="anchor-content"
        class="page-content">
        <p>
            Your Magento version is x.x.x, there is a newer version available. Please note that the upgrade process
            affects only original "out of the box" Magento modules.
        </p>

        <div class="form-wrap">
            <div class="row">
                <div class="col-m-4">
                    <label class="form-label form-el-label-horizontal required" for="storeTimezone">Choose a version</label>
                    <label class="form-select-label" for="storeTimezone">
                        <select
                            id="storeTimezone"
                            class="form-el-select">
                            <option value="203">Version 2.0.3 (latest)</option>
                            <option value="202">Version 2.0.2</option>
                            <option value="201">Version 2.0.1</option>
                            <option value="200">Version 2.0.0</option>
                        </select>
                    </label>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-m-8">
                <h2 class="list-title">What’s New in version 2.0.3</h2>
                <ul class="list list-dot">
                    <li class="list-item">
                        Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
                    </li>
                    <li class="list-item">
                        Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in
                    </li>
                    <li class="list-item">
                        reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in
                    </li>
                    <li class="list-item">
                        culpa qui officia deserunt mollit anim id est laborum.
                        <a href="#">... More</a>
                    </li>
                </ul>
            </div>
        </div>

        <button
            class="btn btn-large btn-prime"
            type="button">Continue</button>

    </main>
</div>
</body>
</html>

