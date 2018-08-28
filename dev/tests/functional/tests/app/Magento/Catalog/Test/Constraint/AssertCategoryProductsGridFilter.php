





<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
  <link rel="dns-prefetch" href="https://assets-cdn.github.com">
  <link rel="dns-prefetch" href="https://avatars0.githubusercontent.com">
  <link rel="dns-prefetch" href="https://avatars1.githubusercontent.com">
  <link rel="dns-prefetch" href="https://avatars2.githubusercontent.com">
  <link rel="dns-prefetch" href="https://avatars3.githubusercontent.com">
  <link rel="dns-prefetch" href="https://github-cloud.s3.amazonaws.com">
  <link rel="dns-prefetch" href="https://user-images.githubusercontent.com/">



  <link crossorigin="anonymous" media="all" integrity="sha512-hqbuBb0QOOmiWgl8a1V1N5q6TI/G0A2hVt/lCFYafR+fYsuXeRUcsdcb/yUyVEHYXktmUXl0Mx9s/BOUNZVq4w==" rel="stylesheet" href="https://assets-cdn.github.com/assets/frameworks-23c9e7262eee71bc6f67f6950190a162.css" />
  <link crossorigin="anonymous" media="all" integrity="sha512-3hVGKwp3EP1XYe7jtewDAU5o/gNueZ3Xm7TL5zKnDaYFi8IeOCqIGitZS1FxnwJp75FLsc7VZSVUbez2mbopJg==" rel="stylesheet" href="https://assets-cdn.github.com/assets/github-9c3638342476c515de9c43a886aa2754.css" />
  
  
  <link crossorigin="anonymous" media="all" integrity="sha512-cCY9KKeDzfGd+snVDZwcOIrTjK+JAFW4FR1c4OGJo1Z2QZNbAGVD6JlcnHL19LBcdByWHVxwrAPwigT/v/DWyQ==" rel="stylesheet" href="https://assets-cdn.github.com/assets/site-7472e7b4603d4095447d49d428375ab8.css" />
  

  <meta name="viewport" content="width=device-width">
  
  <title>magento2/AssertCategoryProductsGridFilter.php at 578e62cf9753b5e74d02f4df36da5865738600f6 · magento/magento2 · GitHub</title>
    <meta name="description" content="GitHub is where people build software. More than 27 million people use GitHub to discover, fork, and contribute to over 80 million projects.">
  <link rel="search" type="application/opensearchdescription+xml" href="/opensearch.xml" title="GitHub">
  <link rel="fluid-icon" href="https://github.com/fluidicon.png" title="GitHub">
  <meta property="fb:app_id" content="1401488693436528">

    
    <meta property="og:image" content="https://avatars0.githubusercontent.com/u/168457?s=400&amp;v=4" /><meta property="og:site_name" content="GitHub" /><meta property="og:type" content="object" /><meta property="og:title" content="magento/magento2" /><meta property="og:url" content="https://github.com/magento/magento2" /><meta property="og:description" content="All Submissions you make to Magento Inc. (“Magento&amp;quot;) through GitHub are subject to the following terms and conditions: (1) You grant Magento a perpetual, worldwide, non-exclusive, no charge, r..." />

  <link rel="assets" href="https://assets-cdn.github.com/">
  
  <meta name="pjax-timeout" content="1000">
  
  <meta name="request-id" content="E736:0800:31EE5C0:6214F75:5B043198" data-pjax-transient>


  

  <meta name="selected-link" value="repo_source" data-pjax-transient>

    <meta name="google-site-verification" content="KT5gs8h0wvaagLKAVWq8bbeNwnZZK1r1XQysX3xurLU">
  <meta name="google-site-verification" content="ZzhVyEFwb7w3e0-uOTltm8Jsck2F5StVihD0exw2fsA">
  <meta name="google-site-verification" content="GXs5KoUUkNCoaAZn7wPN-t01Pywp9M3sEjnt_3_ZWPc">
    <meta name="google-analytics" content="UA-3769691-2">

<meta name="octolytics-host" content="collector.githubapp.com" /><meta name="octolytics-app-id" content="github" /><meta name="octolytics-event-url" content="https://collector.githubapp.com/github-external/browser_event" /><meta name="octolytics-dimension-request_id" content="E736:0800:31EE5C0:6214F75:5B043198" /><meta name="octolytics-dimension-region_edge" content="iad" /><meta name="octolytics-dimension-region_render" content="iad" />
<meta name="analytics-location" content="/&lt;user-name&gt;/&lt;repo-name&gt;/blob/show" data-pjax-transient="true" />




  <meta class="js-ga-set" name="dimension1" content="Logged Out">


  

      <meta name="hostname" content="github.com">
    <meta name="user-login" content="">

      <meta name="expected-hostname" content="github.com">
    <meta name="js-proxy-site-detection-payload" content="Yzk2ZWZlMzg4ZGU0MDgxYzdjMTc3Njc5Y2FmZmJkZmM2N2ZiZTcwMzk4NWYwOGQ2OWI1NjA3YWFkMjQwYTIxOHx7InJlbW90ZV9hZGRyZXNzIjoiOTUuOTguMjAwLjEwOCIsInJlcXVlc3RfaWQiOiJFNzM2OjA4MDA6MzFFRTVDMDo2MjE0Rjc1OjVCMDQzMTk4IiwidGltZXN0YW1wIjoxNTI3MDAxNDk5LCJob3N0IjoiZ2l0aHViLmNvbSJ9">

    <meta name="enabled-features" content="ASYNC_NEWS_FEED,UNIVERSE_BANNER,FREE_TRIALS,MARKETPLACE_INSIGHTS,MARKETPLACE_SELF_SERVE,MARKETPLACE_INSIGHTS_CONVERSION_PERCENTAGES,NEWS_FEED_EVENT_ROLLUPS,STAR_FROM_NEWS_FEED,FOLLOW_FROM_NEWS_FEED">

  <meta name="html-safe-nonce" content="5cdbf28cdb42a8815c5308501bb71a22417c4e95">

  <meta http-equiv="x-pjax-version" content="43224e8c105deecf285e4bd9cde870aa">
  

      <link href="https://github.com/magento/magento2/commits/578e62cf9753b5e74d02f4df36da5865738600f6.atom" rel="alternate" title="Recent Commits to magento2:578e62cf9753b5e74d02f4df36da5865738600f6" type="application/atom+xml">

  <meta name="description" content="All Submissions you make to Magento Inc. (“Magento&quot;) through GitHub are subject to the following terms and conditions: (1) You grant Magento a perpetual, worldwide, non-exclusive, no charge, royalty free, irrevocable license under your applicable copyrights and patents to reproduce, prepare derivative works of, display, publically perform, sublicense and distribute any feedback, ideas, code, or other information (“Submission&quot;) you submit through GitHub. (2) Your Submission is an original work of authorship and you are the owner or are legally entitled to grant the license stated above. (3) You agree to the Contributor License Agreement found here:  https://github.com/magento/magento2/blob/master/CONTRIBUTOR_LICENSE_AGREEMENT.html">
  <meta name="go-import" content="github.com/magento/magento2 git https://github.com/magento/magento2.git">

  <meta name="octolytics-dimension-user_id" content="168457" /><meta name="octolytics-dimension-user_login" content="magento" /><meta name="octolytics-dimension-repository_id" content="2884111" /><meta name="octolytics-dimension-repository_nwo" content="magento/magento2" /><meta name="octolytics-dimension-repository_public" content="true" /><meta name="octolytics-dimension-repository_is_fork" content="false" /><meta name="octolytics-dimension-repository_network_root_id" content="2884111" /><meta name="octolytics-dimension-repository_network_root_nwo" content="magento/magento2" /><meta name="octolytics-dimension-repository_explore_github_marketplace_ci_cta_shown" content="false" />


    <link rel="canonical" href="https://github.com/magento/magento2/blob/578e62cf9753b5e74d02f4df36da5865738600f6/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php" data-pjax-transient>


  <meta name="browser-stats-url" content="https://api.github.com/_private/browser/stats">

  <meta name="browser-errors-url" content="https://api.github.com/_private/browser/errors">

  <link rel="mask-icon" href="https://assets-cdn.github.com/pinned-octocat.svg" color="#000000">
  <link rel="icon" type="image/x-icon" class="js-site-favicon" href="https://assets-cdn.github.com/favicon.ico">

<meta name="theme-color" content="#1e2327">



<link rel="manifest" href="/manifest.json" crossOrigin="use-credentials">

  </head>

  <body class="logged-out env-production page-blob">
    

  <div class="position-relative js-header-wrapper ">
    <a href="#start-of-content" tabindex="1" class="px-2 py-4 bg-blue text-white show-on-focus js-skip-to-content">Skip to content</a>
    <div id="js-pjax-loader-bar" class="pjax-loader-bar"><div class="progress"></div></div>

    
    
    



        <header class="Header header-logged-out  position-relative f4 py-3" role="banner">
  <div class="container-lg d-flex px-3">
    <div class="d-flex flex-justify-between flex-items-center">
      <a class="header-logo-invertocat my-0" href="https://github.com/" aria-label="Homepage" data-ga-click="(Logged out) Header, go to homepage, icon:logo-wordmark">
        <svg height="32" class="octicon octicon-mark-github" viewBox="0 0 16 16" version="1.1" width="32" aria-hidden="true"><path fill-rule="evenodd" d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.013 8.013 0 0 0 16 8c0-4.42-3.58-8-8-8z"/></svg>
      </a>

    </div>

    <div class="HeaderMenu HeaderMenu--bright d-flex flex-justify-between flex-auto">
        <nav class="mt-0">
          <ul class="d-flex list-style-none">
              <li class="ml-2">
                <a class="js-selected-navigation-item HeaderNavlink px-0 py-2 m-0" data-ga-click="Header, click, Nav menu - item:features" data-selected-links="/features /features/project-management /features/code-review /features/project-management /features/integrations /features" href="/features">
                  Features
</a>              </li>
              <li class="ml-4">
                <a class="js-selected-navigation-item HeaderNavlink px-0 py-2 m-0" data-ga-click="Header, click, Nav menu - item:business" data-selected-links="/business /business/security /business/customers /business" href="/business">
                  Business
</a>              </li>

              <li class="ml-4">
                <a class="js-selected-navigation-item HeaderNavlink px-0 py-2 m-0" data-ga-click="Header, click, Nav menu - item:explore" data-selected-links="/explore /trending /trending/developers /integrations /integrations/feature/code /integrations/feature/collaborate /integrations/feature/ship showcases showcases_search showcases_landing /explore" href="/explore">
                  Explore
</a>              </li>

              <li class="ml-4">
                    <a class="js-selected-navigation-item HeaderNavlink px-0 py-2 m-0" data-ga-click="Header, click, Nav menu - item:marketplace" data-selected-links=" /marketplace" href="/marketplace">
                      Marketplace
</a>              </li>
              <li class="ml-4">
                <a class="js-selected-navigation-item HeaderNavlink px-0 py-2 m-0" data-ga-click="Header, click, Nav menu - item:pricing" data-selected-links="/pricing /pricing/developer /pricing/team /pricing/business-hosted /pricing/business-enterprise /pricing" href="/pricing">
                  Pricing
</a>              </li>
          </ul>
        </nav>

      <div class="d-flex">
          <div class="d-lg-flex flex-items-center mr-3">
            <div class="header-search scoped-search site-scoped-search js-site-search" role="search">
  <!-- '"` --><!-- </textarea></xmp> --></option></form><form class="js-site-search-form" data-scope-type="Repository" data-scope-id="2884111" data-scoped-search-url="/magento/magento2/search" data-unscoped-search-url="/search" action="/magento/magento2/search" accept-charset="UTF-8" method="get"><input name="utf8" type="hidden" value="&#x2713;" />
    <label class="form-control header-search-wrapper  js-chromeless-input-container">
          <a class="header-search-scope no-underline" href="/magento/magento2/blob/578e62cf9753b5e74d02f4df36da5865738600f6/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php">This repository</a>
      <input type="text"
        class="form-control header-search-input  js-site-search-focus js-site-search-field is-clearable"
        data-hotkey="s,/"
        name="q"
        value=""
        placeholder="Search"
        aria-label="Search this repository"
        data-unscoped-placeholder="Search GitHub"
        data-scoped-placeholder="Search"
        autocapitalize="off"
        >
        <input type="hidden" class="js-site-search-type-field" name="type" >
    </label>
</form></div>

          </div>

        <span class="d-inline-block">
            <div class="HeaderNavlink px-0 py-2 m-0">
              <a class="text-bold text-white no-underline" href="/login?return_to=%2Fmagento%2Fmagento2%2Fblob%2F578e62cf9753b5e74d02f4df36da5865738600f6%2Fdev%2Ftests%2Ffunctional%2Ftests%2Fapp%2FMagento%2FCatalog%2FTest%2FConstraint%2FAssertCategoryProductsGridFilter.php" data-ga-click="(Logged out) Header, clicked Sign in, text:sign-in">Sign in</a>
                <span class="text-gray">or</span>
                <a class="text-bold text-white no-underline" href="/join?source=header-repo" data-ga-click="(Logged out) Header, clicked Sign up, text:sign-up">Sign up</a>
            </div>
        </span>
      </div>
    </div>
  </div>
</header>

  </div>

  <div id="start-of-content" class="show-on-focus"></div>

    <div id="js-flash-container">
</div>



  <div role="main" class="application-main ">
        <div itemscope itemtype="http://schema.org/SoftwareSourceCode" class="">
    <div id="js-repo-pjax-container" data-pjax-container >
      





  



  <div class="pagehead repohead instapaper_ignore readability-menu experiment-repo-nav  ">
    <div class="repohead-details-container clearfix container">

      <ul class="pagehead-actions">
  <li>
      <a href="/login?return_to=%2Fmagento%2Fmagento2"
    class="btn btn-sm btn-with-count tooltipped tooltipped-n"
    aria-label="You must be signed in to watch a repository" rel="nofollow">
    <svg class="octicon octicon-eye" viewBox="0 0 16 16" version="1.1" width="16" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M8.06 2C3 2 0 8 0 8s3 6 8.06 6C13 14 16 8 16 8s-3-6-7.94-6zM8 12c-2.2 0-4-1.78-4-4 0-2.2 1.8-4 4-4 2.22 0 4 1.8 4 4 0 2.22-1.78 4-4 4zm2-4c0 1.11-.89 2-2 2-1.11 0-2-.89-2-2 0-1.11.89-2 2-2 1.11 0 2 .89 2 2z"/></svg>
    Watch
  </a>
  <a class="social-count" href="/magento/magento2/watchers"
     aria-label="1121 users are watching this repository">
    1,121
  </a>

  </li>

  <li>
      <a href="/login?return_to=%2Fmagento%2Fmagento2"
    class="btn btn-sm btn-with-count tooltipped tooltipped-n"
    aria-label="You must be signed in to star a repository" rel="nofollow">
    <svg class="octicon octicon-star" viewBox="0 0 14 16" version="1.1" width="14" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M14 6l-4.9-.64L7 1 4.9 5.36 0 6l3.6 3.26L2.67 14 7 11.67 11.33 14l-.93-4.74L14 6z"/></svg>
    Star
  </a>

    <a class="social-count js-social-count" href="/magento/magento2/stargazers"
      aria-label="5642 users starred this repository">
      5,642
    </a>

  </li>

  <li>
      <a href="/login?return_to=%2Fmagento%2Fmagento2"
        class="btn btn-sm btn-with-count tooltipped tooltipped-n"
        aria-label="You must be signed in to fork a repository" rel="nofollow">
        <svg class="octicon octicon-repo-forked" viewBox="0 0 10 16" version="1.1" width="10" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M8 1a1.993 1.993 0 0 0-1 3.72V6L5 8 3 6V4.72A1.993 1.993 0 0 0 2 1a1.993 1.993 0 0 0-1 3.72V6.5l3 3v1.78A1.993 1.993 0 0 0 5 15a1.993 1.993 0 0 0 1-3.72V9.5l3-3V4.72A1.993 1.993 0 0 0 8 1zM2 4.2C1.34 4.2.8 3.65.8 3c0-.65.55-1.2 1.2-1.2.65 0 1.2.55 1.2 1.2 0 .65-.55 1.2-1.2 1.2zm3 10c-.66 0-1.2-.55-1.2-1.2 0-.65.55-1.2 1.2-1.2.65 0 1.2.55 1.2 1.2 0 .65-.55 1.2-1.2 1.2zm3-10c-.66 0-1.2-.55-1.2-1.2 0-.65.55-1.2 1.2-1.2.65 0 1.2.55 1.2 1.2 0 .65-.55 1.2-1.2 1.2z"/></svg>
        Fork
      </a>

    <a href="/magento/magento2/network" class="social-count"
       aria-label="4723 users forked this repository">
      4,723
    </a>
  </li>
</ul>

      <h1 class="public ">
  <svg class="octicon octicon-repo" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M4 9H3V8h1v1zm0-3H3v1h1V6zm0-2H3v1h1V4zm0-2H3v1h1V2zm8-1v12c0 .55-.45 1-1 1H6v2l-1.5-1.5L3 16v-2H1c-.55 0-1-.45-1-1V1c0-.55.45-1 1-1h10c.55 0 1 .45 1 1zm-1 10H1v2h2v-1h3v1h5v-2zm0-10H2v9h9V1z"/></svg>
  <span class="author" itemprop="author"><a class="url fn" rel="author" href="/magento">magento</a></span><!--
--><span class="path-divider">/</span><!--
--><strong itemprop="name"><a data-pjax="#js-repo-pjax-container" href="/magento/magento2">magento2</a></strong>

</h1>

    </div>
    
<nav class="reponav js-repo-nav js-sidenav-container-pjax container"
     itemscope
     itemtype="http://schema.org/BreadcrumbList"
     role="navigation"
     data-pjax="#js-repo-pjax-container">

  <span itemscope itemtype="http://schema.org/ListItem" itemprop="itemListElement">
    <a class="js-selected-navigation-item selected reponav-item" itemprop="url" data-hotkey="g c" data-selected-links="repo_source repo_downloads repo_commits repo_releases repo_tags repo_branches repo_packages /magento/magento2" href="/magento/magento2">
      <svg class="octicon octicon-code" viewBox="0 0 14 16" version="1.1" width="14" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M9.5 3L8 4.5 11.5 8 8 11.5 9.5 13 14 8 9.5 3zm-5 0L0 8l4.5 5L6 11.5 2.5 8 6 4.5 4.5 3z"/></svg>
      <span itemprop="name">Code</span>
      <meta itemprop="position" content="1">
</a>  </span>

    <span itemscope itemtype="http://schema.org/ListItem" itemprop="itemListElement">
      <a itemprop="url" data-hotkey="g i" class="js-selected-navigation-item reponav-item" data-selected-links="repo_issues repo_labels repo_milestones /magento/magento2/issues" href="/magento/magento2/issues">
        <svg class="octicon octicon-issue-opened" viewBox="0 0 14 16" version="1.1" width="14" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M7 2.3c3.14 0 5.7 2.56 5.7 5.7s-2.56 5.7-5.7 5.7A5.71 5.71 0 0 1 1.3 8c0-3.14 2.56-5.7 5.7-5.7zM7 1C3.14 1 0 4.14 0 8s3.14 7 7 7 7-3.14 7-7-3.14-7-7-7zm1 3H6v5h2V4zm0 6H6v2h2v-2z"/></svg>
        <span itemprop="name">Issues</span>
        <span class="Counter">1,262</span>
        <meta itemprop="position" content="2">
</a>    </span>

  <span itemscope itemtype="http://schema.org/ListItem" itemprop="itemListElement">
    <a data-hotkey="g p" itemprop="url" class="js-selected-navigation-item reponav-item" data-selected-links="repo_pulls checks /magento/magento2/pulls" href="/magento/magento2/pulls">
      <svg class="octicon octicon-git-pull-request" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M11 11.28V5c-.03-.78-.34-1.47-.94-2.06C9.46 2.35 8.78 2.03 8 2H7V0L4 3l3 3V4h1c.27.02.48.11.69.31.21.2.3.42.31.69v6.28A1.993 1.993 0 0 0 10 15a1.993 1.993 0 0 0 1-3.72zm-1 2.92c-.66 0-1.2-.55-1.2-1.2 0-.65.55-1.2 1.2-1.2.65 0 1.2.55 1.2 1.2 0 .65-.55 1.2-1.2 1.2zM4 3c0-1.11-.89-2-2-2a1.993 1.993 0 0 0-1 3.72v6.56A1.993 1.993 0 0 0 2 15a1.993 1.993 0 0 0 1-3.72V4.72c.59-.34 1-.98 1-1.72zm-.8 10c0 .66-.55 1.2-1.2 1.2-.65 0-1.2-.55-1.2-1.2 0-.65.55-1.2 1.2-1.2.65 0 1.2.55 1.2 1.2zM2 4.2C1.34 4.2.8 3.65.8 3c0-.65.55-1.2 1.2-1.2.65 0 1.2.55 1.2 1.2 0 .65-.55 1.2-1.2 1.2z"/></svg>
      <span itemprop="name">Pull requests</span>
      <span class="Counter">236</span>
      <meta itemprop="position" content="3">
</a>  </span>

    <a data-hotkey="g b" class="js-selected-navigation-item reponav-item" data-selected-links="repo_projects new_repo_project repo_project /magento/magento2/projects" href="/magento/magento2/projects">
      <svg class="octicon octicon-project" viewBox="0 0 15 16" version="1.1" width="15" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M10 12h3V2h-3v10zm-4-2h3V2H6v8zm-4 4h3V2H2v12zm-1 1h13V1H1v14zM14 0H1a1 1 0 0 0-1 1v14a1 1 0 0 0 1 1h13a1 1 0 0 0 1-1V1a1 1 0 0 0-1-1z"/></svg>
      Projects
      <span class="Counter" >2</span>
</a>
    <a class="js-selected-navigation-item reponav-item" data-hotkey="g w" data-selected-links="repo_wiki /magento/magento2/wiki" href="/magento/magento2/wiki">
      <svg class="octicon octicon-book" viewBox="0 0 16 16" version="1.1" width="16" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M3 5h4v1H3V5zm0 3h4V7H3v1zm0 2h4V9H3v1zm11-5h-4v1h4V5zm0 2h-4v1h4V7zm0 2h-4v1h4V9zm2-6v9c0 .55-.45 1-1 1H9.5l-1 1-1-1H2c-.55 0-1-.45-1-1V3c0-.55.45-1 1-1h5.5l1 1 1-1H15c.55 0 1 .45 1 1zm-8 .5L7.5 3H2v9h6V3.5zm7-.5H9.5l-.5.5V12h6V3z"/></svg>
      Wiki
</a>

  <a class="js-selected-navigation-item reponav-item" data-selected-links="repo_graphs repo_contributors dependency_graph pulse /magento/magento2/pulse" href="/magento/magento2/pulse">
    <svg class="octicon octicon-graph" viewBox="0 0 16 16" version="1.1" width="16" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M16 14v1H0V0h1v14h15zM5 13H3V8h2v5zm4 0H7V3h2v10zm4 0h-2V6h2v7z"/></svg>
    Insights
</a>

</nav>


  </div>

<div class="container new-discussion-timeline experiment-repo-nav  ">
  <div class="repository-content ">

    
  <a class="d-none js-permalink-shortcut" data-hotkey="y" href="/magento/magento2/blob/578e62cf9753b5e74d02f4df36da5865738600f6/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php">Permalink</a>

  <!-- blob contrib key: blob_contributors:v21:cfd43c834fad2b10333e866a9d2f1443 -->

  <div class="file-navigation">
    
<div class="select-menu branch-select-menu js-menu-container js-select-menu float-left">
  <button class=" btn btn-sm select-menu-button js-menu-target css-truncate" data-hotkey="w"
    
    type="button" aria-label="Switch branches or tags" aria-expanded="false" aria-haspopup="true">
      <i>Tree:</i>
      <span class="js-select-button css-truncate-target">578e62cf97</span>
  </button>

  <div class="select-menu-modal-holder js-menu-content js-navigation-container" data-pjax>

    <div class="select-menu-modal">
      <div class="select-menu-header">
        <svg class="octicon octicon-x js-menu-close" role="img" aria-label="Close" viewBox="0 0 12 16" version="1.1" width="12" height="16"><path fill-rule="evenodd" d="M7.48 8l3.75 3.75-1.48 1.48L6 9.48l-3.75 3.75-1.48-1.48L4.52 8 .77 4.25l1.48-1.48L6 6.52l3.75-3.75 1.48 1.48L7.48 8z"/></svg>
        <span class="select-menu-title">Switch branches/tags</span>
      </div>

      <div class="select-menu-filters">
        <div class="select-menu-text-filter">
          <input type="text" aria-label="Filter branches/tags" id="context-commitish-filter-field" class="form-control js-filterable-field js-navigation-enable" placeholder="Filter branches/tags">
        </div>
        <div class="select-menu-tabs">
          <ul>
            <li class="select-menu-tab">
              <a href="#" data-tab-filter="branches" data-filter-placeholder="Filter branches/tags" class="js-select-menu-tab" role="tab">Branches</a>
            </li>
            <li class="select-menu-tab">
              <a href="#" data-tab-filter="tags" data-filter-placeholder="Find a tag…" class="js-select-menu-tab" role="tab">Tags</a>
            </li>
          </ul>
        </div>
      </div>

      <div class="select-menu-list select-menu-tab-bucket js-select-menu-tab-bucket" data-tab-filter="branches" role="menu">

        <div data-filterable-for="context-commitish-filter-field" data-filterable-type="substring">


            <a class="select-menu-item js-navigation-item js-navigation-open "
               href="/magento/magento2/blob/2.0/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
               data-name="2.0"
               data-skip-pjax="true"
               rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target js-select-menu-filter-text">
                2.0
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
               href="/magento/magento2/blob/2.1-develop/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
               data-name="2.1-develop"
               data-skip-pjax="true"
               rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target js-select-menu-filter-text">
                2.1-develop
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
               href="/magento/magento2/blob/2.1/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
               data-name="2.1"
               data-skip-pjax="true"
               rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target js-select-menu-filter-text">
                2.1
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
               href="/magento/magento2/blob/2.2-develop/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
               data-name="2.2-develop"
               data-skip-pjax="true"
               rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target js-select-menu-filter-text">
                2.2-develop
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
               href="/magento/magento2/blob/2.2/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
               data-name="2.2"
               data-skip-pjax="true"
               rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target js-select-menu-filter-text">
                2.2
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
               href="/magento/magento2/blob/2.2.4-develop/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
               data-name="2.2.4-develop"
               data-skip-pjax="true"
               rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target js-select-menu-filter-text">
                2.2.4-develop
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
               href="/magento/magento2/blob/2.2.4-preview/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
               data-name="2.2.4-preview"
               data-skip-pjax="true"
               rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target js-select-menu-filter-text">
                2.2.4-preview
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
               href="/magento/magento2/blob/2.2.6/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
               data-name="2.2.6"
               data-skip-pjax="true"
               rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target js-select-menu-filter-text">
                2.2.6
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
               href="/magento/magento2/blob/2.3-develop/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
               data-name="2.3-develop"
               data-skip-pjax="true"
               rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target js-select-menu-filter-text">
                2.3-develop
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
               href="/magento/magento2/blob/checkout-issue-1/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
               data-name="checkout-issue-1"
               data-skip-pjax="true"
               rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target js-select-menu-filter-text">
                checkout-issue-1
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
               href="/magento/magento2/blob/experius-2.2-patch-issue-14966/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
               data-name="experius-2.2-patch-issue-14966"
               data-skip-pjax="true"
               rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target js-select-menu-filter-text">
                experius-2.2-patch-issue-14966
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
               href="/magento/magento2/blob/issue-7897/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
               data-name="issue-7897"
               data-skip-pjax="true"
               rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target js-select-menu-filter-text">
                issue-7897
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
               href="/magento/magento2/blob/template-issue/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
               data-name="template-issue"
               data-skip-pjax="true"
               rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target js-select-menu-filter-text">
                template-issue
              </span>
            </a>
        </div>

          <div class="select-menu-no-results">Nothing to show</div>
      </div>

      <div class="select-menu-list select-menu-tab-bucket js-select-menu-tab-bucket" data-tab-filter="tags">
        <div data-filterable-for="context-commitish-filter-field" data-filterable-type="substring">


            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/2.2.4/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="2.2.4"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="2.2.4">
                2.2.4
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/2.2.3/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="2.2.3"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="2.2.3">
                2.2.3
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/2.2.2/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="2.2.2"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="2.2.2">
                2.2.2
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/2.2.1/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="2.2.1"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="2.2.1">
                2.2.1
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/2.2.0/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="2.2.0"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="2.2.0">
                2.2.0
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/2.2.0-rc3.0/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="2.2.0-rc3.0"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="2.2.0-rc3.0">
                2.2.0-rc3.0
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/2.2.0-rc2.3/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="2.2.0-rc2.3"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="2.2.0-rc2.3">
                2.2.0-rc2.3
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/2.2.0-rc2.2/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="2.2.0-rc2.2"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="2.2.0-rc2.2">
                2.2.0-rc2.2
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/2.2.0-rc2.1/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="2.2.0-rc2.1"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="2.2.0-rc2.1">
                2.2.0-rc2.1
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/2.2.0-rc2.0/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="2.2.0-rc2.0"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="2.2.0-rc2.0">
                2.2.0-rc2.0
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/2.2.0-RC1.8/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="2.2.0-RC1.8"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="2.2.0-RC1.8">
                2.2.0-RC1.8
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/2.2.0-RC1.6/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="2.2.0-RC1.6"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="2.2.0-RC1.6">
                2.2.0-RC1.6
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/2.2.0-RC1.5/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="2.2.0-RC1.5"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="2.2.0-RC1.5">
                2.2.0-RC1.5
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/2.2.0-RC1.4/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="2.2.0-RC1.4"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="2.2.0-RC1.4">
                2.2.0-RC1.4
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/2.2.0-RC1.3/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="2.2.0-RC1.3"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="2.2.0-RC1.3">
                2.2.0-RC1.3
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/2.2.0-RC1.2/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="2.2.0-RC1.2"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="2.2.0-RC1.2">
                2.2.0-RC1.2
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/2.2.0-RC1.1/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="2.2.0-RC1.1"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="2.2.0-RC1.1">
                2.2.0-RC1.1
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/2.1.13/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="2.1.13"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="2.1.13">
                2.1.13
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/2.1.12/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="2.1.12"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="2.1.12">
                2.1.12
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/2.1.11/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="2.1.11"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="2.1.11">
                2.1.11
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/2.1.10/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="2.1.10"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="2.1.10">
                2.1.10
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/2.1.9/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="2.1.9"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="2.1.9">
                2.1.9
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/2.1.8/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="2.1.8"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="2.1.8">
                2.1.8
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/2.1.7/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="2.1.7"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="2.1.7">
                2.1.7
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/2.1.6/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="2.1.6"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="2.1.6">
                2.1.6
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/2.1.5/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="2.1.5"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="2.1.5">
                2.1.5
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/2.1.4/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="2.1.4"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="2.1.4">
                2.1.4
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/2.1.3/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="2.1.3"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="2.1.3">
                2.1.3
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/2.1.2/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="2.1.2"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="2.1.2">
                2.1.2
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/2.1.1/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="2.1.1"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="2.1.1">
                2.1.1
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/2.1.0/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="2.1.0"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="2.1.0">
                2.1.0
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/2.1.0-rc3/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="2.1.0-rc3"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="2.1.0-rc3">
                2.1.0-rc3
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/2.1.0-rc2/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="2.1.0-rc2"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="2.1.0-rc2">
                2.1.0-rc2
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/2.1.0-rc1/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="2.1.0-rc1"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="2.1.0-rc1">
                2.1.0-rc1
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/2.0.18/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="2.0.18"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="2.0.18">
                2.0.18
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/2.0.17/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="2.0.17"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="2.0.17">
                2.0.17
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/2.0.16/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="2.0.16"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="2.0.16">
                2.0.16
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/2.0.15/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="2.0.15"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="2.0.15">
                2.0.15
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/2.0.14/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="2.0.14"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="2.0.14">
                2.0.14
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/2.0.13/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="2.0.13"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="2.0.13">
                2.0.13
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/2.0.12/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="2.0.12"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="2.0.12">
                2.0.12
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/2.0.11/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="2.0.11"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="2.0.11">
                2.0.11
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/2.0.10/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="2.0.10"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="2.0.10">
                2.0.10
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/2.0.9/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="2.0.9"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="2.0.9">
                2.0.9
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/2.0.8/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="2.0.8"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="2.0.8">
                2.0.8
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/2.0.7/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="2.0.7"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="2.0.7">
                2.0.7
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/2.0.6/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="2.0.6"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="2.0.6">
                2.0.6
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/2.0.5/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="2.0.5"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="2.0.5">
                2.0.5
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/2.0.4/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="2.0.4"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="2.0.4">
                2.0.4
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/2.0.3/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="2.0.3"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="2.0.3">
                2.0.3
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/2.0.2/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="2.0.2"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="2.0.2">
                2.0.2
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/2.0.1/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="2.0.1"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="2.0.1">
                2.0.1
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/2.0.0/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="2.0.0"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="2.0.0">
                2.0.0
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/2.0.0-rc2/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="2.0.0-rc2"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="2.0.0-rc2">
                2.0.0-rc2
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/2.0.0-rc/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="2.0.0-rc"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="2.0.0-rc">
                2.0.0-rc
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/1.0.0-beta6/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="1.0.0-beta6"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="1.0.0-beta6">
                1.0.0-beta6
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/1.0.0-beta5/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="1.0.0-beta5"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="1.0.0-beta5">
                1.0.0-beta5
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/1.0.0-beta4/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="1.0.0-beta4"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="1.0.0-beta4">
                1.0.0-beta4
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/1.0.0-beta3/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="1.0.0-beta3"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="1.0.0-beta3">
                1.0.0-beta3
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/1.0.0-beta2/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="1.0.0-beta2"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="1.0.0-beta2">
                1.0.0-beta2
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/1.0.0-beta/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="1.0.0-beta"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="1.0.0-beta">
                1.0.0-beta
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/0.74.0-beta16/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="0.74.0-beta16"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="0.74.0-beta16">
                0.74.0-beta16
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/0.74.0-beta15/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="0.74.0-beta15"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="0.74.0-beta15">
                0.74.0-beta15
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/0.74.0-beta14/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="0.74.0-beta14"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="0.74.0-beta14">
                0.74.0-beta14
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/0.74.0-beta13/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="0.74.0-beta13"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="0.74.0-beta13">
                0.74.0-beta13
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/0.74.0-beta12/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="0.74.0-beta12"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="0.74.0-beta12">
                0.74.0-beta12
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/0.74.0-beta11/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="0.74.0-beta11"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="0.74.0-beta11">
                0.74.0-beta11
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/0.74.0-beta10/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="0.74.0-beta10"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="0.74.0-beta10">
                0.74.0-beta10
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/0.74.0-beta9/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="0.74.0-beta9"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="0.74.0-beta9">
                0.74.0-beta9
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/0.74.0-beta8/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="0.74.0-beta8"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="0.74.0-beta8">
                0.74.0-beta8
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/0.74.0-beta7/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="0.74.0-beta7"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="0.74.0-beta7">
                0.74.0-beta7
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/0.74.0-beta6/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="0.74.0-beta6"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="0.74.0-beta6">
                0.74.0-beta6
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/0.74.0-beta5/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="0.74.0-beta5"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="0.74.0-beta5">
                0.74.0-beta5
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/0.74.0-beta4/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="0.74.0-beta4"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="0.74.0-beta4">
                0.74.0-beta4
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/0.74.0-beta3/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="0.74.0-beta3"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="0.74.0-beta3">
                0.74.0-beta3
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/0.74.0-beta2/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="0.74.0-beta2"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="0.74.0-beta2">
                0.74.0-beta2
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/0.74.0-beta1/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="0.74.0-beta1"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="0.74.0-beta1">
                0.74.0-beta1
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/0.42.0-beta11/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="0.42.0-beta11"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="0.42.0-beta11">
                0.42.0-beta11
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/0.42.0-beta10/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="0.42.0-beta10"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="0.42.0-beta10">
                0.42.0-beta10
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/0.42.0-beta9/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="0.42.0-beta9"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="0.42.0-beta9">
                0.42.0-beta9
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/0.42.0-beta8/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="0.42.0-beta8"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="0.42.0-beta8">
                0.42.0-beta8
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/0.42.0-beta7/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="0.42.0-beta7"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="0.42.0-beta7">
                0.42.0-beta7
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/0.42.0-beta6/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="0.42.0-beta6"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="0.42.0-beta6">
                0.42.0-beta6
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/0.42.0-beta5/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="0.42.0-beta5"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="0.42.0-beta5">
                0.42.0-beta5
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/0.42.0-beta4/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="0.42.0-beta4"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="0.42.0-beta4">
                0.42.0-beta4
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/0.42.0-beta3/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="0.42.0-beta3"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="0.42.0-beta3">
                0.42.0-beta3
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/0.42.0-beta2/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="0.42.0-beta2"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="0.42.0-beta2">
                0.42.0-beta2
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/0.42.0-beta1/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="0.42.0-beta1"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="0.42.0-beta1">
                0.42.0-beta1
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/0.1.0-alpha108/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="0.1.0-alpha108"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="0.1.0-alpha108">
                0.1.0-alpha108
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/0.1.0-alpha107/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="0.1.0-alpha107"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="0.1.0-alpha107">
                0.1.0-alpha107
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/0.1.0-alpha106/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="0.1.0-alpha106"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="0.1.0-alpha106">
                0.1.0-alpha106
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/0.1.0-alpha105/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="0.1.0-alpha105"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="0.1.0-alpha105">
                0.1.0-alpha105
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/0.1.0-alpha104/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="0.1.0-alpha104"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="0.1.0-alpha104">
                0.1.0-alpha104
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/0.1.0-alpha103/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="0.1.0-alpha103"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="0.1.0-alpha103">
                0.1.0-alpha103
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/0.1.0-alpha102/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="0.1.0-alpha102"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="0.1.0-alpha102">
                0.1.0-alpha102
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/0.1.0-alpha101/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="0.1.0-alpha101"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="0.1.0-alpha101">
                0.1.0-alpha101
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/0.1.0-alpha100/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="0.1.0-alpha100"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="0.1.0-alpha100">
                0.1.0-alpha100
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/0.1.0-alpha99/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="0.1.0-alpha99"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="0.1.0-alpha99">
                0.1.0-alpha99
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/0.1.0-alpha98/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="0.1.0-alpha98"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="0.1.0-alpha98">
                0.1.0-alpha98
              </span>
            </a>
            <a class="select-menu-item js-navigation-item js-navigation-open "
              href="/magento/magento2/tree/0.1.0-alpha97/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php"
              data-name="0.1.0-alpha97"
              data-skip-pjax="true"
              rel="nofollow">
              <svg class="octicon octicon-check select-menu-item-icon" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 5l-8 8-4-4 1.5-1.5L4 10l6.5-6.5L12 5z"/></svg>
              <span class="select-menu-item-text css-truncate-target" title="0.1.0-alpha97">
                0.1.0-alpha97
              </span>
            </a>
        </div>

        <div class="select-menu-no-results">Nothing to show</div>
      </div>

    </div>
  </div>
</div>

    <div class="BtnGroup float-right">
      <a href="/magento/magento2/find/578e62cf9753b5e74d02f4df36da5865738600f6"
            class="js-pjax-capture-input btn btn-sm BtnGroup-item"
            data-pjax
            data-hotkey="t">
        Find file
      </a>
      <clipboard-copy for="blob-path" class="btn btn-sm BtnGroup-item">
        Copy path
      </clipboard-copy>
    </div>
    <div id="blob-path" class="breadcrumb">
      <span class="repo-root js-repo-root"><span class="js-path-segment"><a data-pjax="true" rel="nofollow" href="/magento/magento2/tree/578e62cf9753b5e74d02f4df36da5865738600f6"><span>magento2</span></a></span></span><span class="separator">/</span><span class="js-path-segment"><a data-pjax="true" rel="nofollow" href="/magento/magento2/tree/578e62cf9753b5e74d02f4df36da5865738600f6/dev"><span>dev</span></a></span><span class="separator">/</span><span class="js-path-segment"><a data-pjax="true" rel="nofollow" href="/magento/magento2/tree/578e62cf9753b5e74d02f4df36da5865738600f6/dev/tests"><span>tests</span></a></span><span class="separator">/</span><span class="js-path-segment"><a data-pjax="true" rel="nofollow" href="/magento/magento2/tree/578e62cf9753b5e74d02f4df36da5865738600f6/dev/tests/functional"><span>functional</span></a></span><span class="separator">/</span><span class="js-path-segment"><a data-pjax="true" rel="nofollow" href="/magento/magento2/tree/578e62cf9753b5e74d02f4df36da5865738600f6/dev/tests/functional/tests"><span>tests</span></a></span><span class="separator">/</span><span class="js-path-segment"><a data-pjax="true" rel="nofollow" href="/magento/magento2/tree/578e62cf9753b5e74d02f4df36da5865738600f6/dev/tests/functional/tests/app"><span>app</span></a></span><span class="separator">/</span><span class="js-path-segment"><a data-pjax="true" rel="nofollow" href="/magento/magento2/tree/578e62cf9753b5e74d02f4df36da5865738600f6/dev/tests/functional/tests/app/Magento"><span>Magento</span></a></span><span class="separator">/</span><span class="js-path-segment"><a data-pjax="true" rel="nofollow" href="/magento/magento2/tree/578e62cf9753b5e74d02f4df36da5865738600f6/dev/tests/functional/tests/app/Magento/Catalog"><span>Catalog</span></a></span><span class="separator">/</span><span class="js-path-segment"><a data-pjax="true" rel="nofollow" href="/magento/magento2/tree/578e62cf9753b5e74d02f4df36da5865738600f6/dev/tests/functional/tests/app/Magento/Catalog/Test"><span>Test</span></a></span><span class="separator">/</span><span class="js-path-segment"><a data-pjax="true" rel="nofollow" href="/magento/magento2/tree/578e62cf9753b5e74d02f4df36da5865738600f6/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint"><span>Constraint</span></a></span><span class="separator">/</span><strong class="final-path">AssertCategoryProductsGridFilter.php</strong>
    </div>
  </div>


  <include-fragment src="/magento/magento2/contributors/578e62cf9753b5e74d02f4df36da5865738600f6/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php" class="commit-tease">
    <div>
      Fetching contributors&hellip;
    </div>

    <div class="commit-tease-contributors">
      <img alt="" class="loader-loading float-left" src="https://assets-cdn.github.com/images/spinners/octocat-spinner-32-EAF2F5.gif" width="16" height="16" />
      <span class="loader-error">Cannot retrieve contributors at this time</span>
    </div>
</include-fragment>


  <div class="file">
    <div class="file-header">
  <div class="file-actions">

    <div class="BtnGroup">
      <a id="raw-url" class="btn btn-sm BtnGroup-item" href="/magento/magento2/raw/578e62cf9753b5e74d02f4df36da5865738600f6/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php">Raw</a>
        <a class="btn btn-sm js-update-url-with-hash BtnGroup-item" data-hotkey="b" href="/magento/magento2/blame/578e62cf9753b5e74d02f4df36da5865738600f6/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php">Blame</a>
      <a rel="nofollow" class="btn btn-sm BtnGroup-item" href="/magento/magento2/commits/578e62cf9753b5e74d02f4df36da5865738600f6/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php">History</a>
    </div>


        <button type="button" class="btn-octicon disabled tooltipped tooltipped-nw"
          aria-label="You must be signed in to make or propose changes">
          <svg class="octicon octicon-pencil" viewBox="0 0 14 16" version="1.1" width="14" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M0 12v3h3l8-8-3-3-8 8zm3 2H1v-2h1v1h1v1zm10.3-9.3L12 6 9 3l1.3-1.3a.996.996 0 0 1 1.41 0l1.59 1.59c.39.39.39 1.02 0 1.41z"/></svg>
        </button>
        <button type="button" class="btn-octicon btn-octicon-danger disabled tooltipped tooltipped-nw"
          aria-label="You must be signed in to make or propose changes">
          <svg class="octicon octicon-trashcan" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M11 2H9c0-.55-.45-1-1-1H5c-.55 0-1 .45-1 1H2c-.55 0-1 .45-1 1v1c0 .55.45 1 1 1v9c0 .55.45 1 1 1h7c.55 0 1-.45 1-1V5c.55 0 1-.45 1-1V3c0-.55-.45-1-1-1zm-1 12H3V5h1v8h1V5h1v8h1V5h1v8h1V5h1v9zm1-10H2V3h9v1z"/></svg>
        </button>
  </div>

  <div class="file-info">
      100 lines (89 sloc)
      <span class="file-info-divider"></span>
    3.15 KB
  </div>
</div>

    

  <div itemprop="text" class="blob-wrapper data type-php">
      <table class="highlight tab-size js-file-line-container" data-tab-size="8">
      <tr>
        <td id="L1" class="blob-num js-line-number" data-line-number="1"></td>
        <td id="LC1" class="blob-code blob-code-inner js-file-line"><span class="pl-pse">&lt;?php</span><span class="pl-s1"></span></td>
      </tr>
      <tr>
        <td id="L2" class="blob-num js-line-number" data-line-number="2"></td>
        <td id="LC2" class="blob-code blob-code-inner js-file-line"><span class="pl-s1"><span class="pl-c"><span class="pl-c">/**</span></span></span></td>
      </tr>
      <tr>
        <td id="L3" class="blob-num js-line-number" data-line-number="3"></td>
        <td id="LC3" class="blob-code blob-code-inner js-file-line"><span class="pl-s1"><span class="pl-c"> * Copyright © Magento, Inc. All rights reserved.</span></span></td>
      </tr>
      <tr>
        <td id="L4" class="blob-num js-line-number" data-line-number="4"></td>
        <td id="LC4" class="blob-code blob-code-inner js-file-line"><span class="pl-s1"><span class="pl-c"> * See COPYING.txt for license details.</span></span></td>
      </tr>
      <tr>
        <td id="L5" class="blob-num js-line-number" data-line-number="5"></td>
        <td id="LC5" class="blob-code blob-code-inner js-file-line"><span class="pl-s1"><span class="pl-c"> <span class="pl-c">*/</span></span></span></td>
      </tr>
      <tr>
        <td id="L6" class="blob-num js-line-number" data-line-number="6"></td>
        <td id="LC6" class="blob-code blob-code-inner js-file-line"><span class="pl-s1"></span></td>
      </tr>
      <tr>
        <td id="L7" class="blob-num js-line-number" data-line-number="7"></td>
        <td id="LC7" class="blob-code blob-code-inner js-file-line"><span class="pl-s1"><span class="pl-k">namespace</span> <span class="pl-en">Magento\Catalog\Test\Constraint</span>;</span></td>
      </tr>
      <tr>
        <td id="L8" class="blob-num js-line-number" data-line-number="8"></td>
        <td id="LC8" class="blob-code blob-code-inner js-file-line"><span class="pl-s1"></span></td>
      </tr>
      <tr>
        <td id="L9" class="blob-num js-line-number" data-line-number="9"></td>
        <td id="LC9" class="blob-code blob-code-inner js-file-line"><span class="pl-s1"><span class="pl-k">use</span> <span class="pl-c1">Magento\Catalog\Test\Fixture\Category</span>;</span></td>
      </tr>
      <tr>
        <td id="L10" class="blob-num js-line-number" data-line-number="10"></td>
        <td id="LC10" class="blob-code blob-code-inner js-file-line"><span class="pl-s1"><span class="pl-k">use</span> <span class="pl-c1">Magento\Catalog\Test\Page\Adminhtml\CatalogCategoryEdit</span>;</span></td>
      </tr>
      <tr>
        <td id="L11" class="blob-num js-line-number" data-line-number="11"></td>
        <td id="LC11" class="blob-code blob-code-inner js-file-line"><span class="pl-s1"><span class="pl-k">use</span> <span class="pl-c1">Magento\Catalog\Test\Page\Adminhtml\CatalogCategoryIndex</span>;</span></td>
      </tr>
      <tr>
        <td id="L12" class="blob-num js-line-number" data-line-number="12"></td>
        <td id="LC12" class="blob-code blob-code-inner js-file-line"><span class="pl-s1"><span class="pl-k">use</span> <span class="pl-c1">Magento\Mtf\Constraint\AbstractConstraint</span>;</span></td>
      </tr>
      <tr>
        <td id="L13" class="blob-num js-line-number" data-line-number="13"></td>
        <td id="LC13" class="blob-code blob-code-inner js-file-line"><span class="pl-s1"></span></td>
      </tr>
      <tr>
        <td id="L14" class="blob-num js-line-number" data-line-number="14"></td>
        <td id="LC14" class="blob-code blob-code-inner js-file-line"><span class="pl-s1"><span class="pl-c"><span class="pl-c">/**</span></span></span></td>
      </tr>
      <tr>
        <td id="L15" class="blob-num js-line-number" data-line-number="15"></td>
        <td id="LC15" class="blob-code blob-code-inner js-file-line"><span class="pl-s1"><span class="pl-c"> * Assert that category products grid filter works correctly.</span></span></td>
      </tr>
      <tr>
        <td id="L16" class="blob-num js-line-number" data-line-number="16"></td>
        <td id="LC16" class="blob-code blob-code-inner js-file-line"><span class="pl-s1"><span class="pl-c"> <span class="pl-c">*/</span></span></span></td>
      </tr>
      <tr>
        <td id="L17" class="blob-num js-line-number" data-line-number="17"></td>
        <td id="LC17" class="blob-code blob-code-inner js-file-line"><span class="pl-s1"><span class="pl-k">class</span> <span class="pl-en">AssertCategoryProductsGridFilter</span> <span class="pl-k">extends</span> <span class="pl-e">AbstractConstraint</span></span></td>
      </tr>
      <tr>
        <td id="L18" class="blob-num js-line-number" data-line-number="18"></td>
        <td id="LC18" class="blob-code blob-code-inner js-file-line"><span class="pl-s1">{</span></td>
      </tr>
      <tr>
        <td id="L19" class="blob-num js-line-number" data-line-number="19"></td>
        <td id="LC19" class="blob-code blob-code-inner js-file-line"><span class="pl-s1">    <span class="pl-c"><span class="pl-c">/**</span></span></span></td>
      </tr>
      <tr>
        <td id="L20" class="blob-num js-line-number" data-line-number="20"></td>
        <td id="LC20" class="blob-code blob-code-inner js-file-line"><span class="pl-s1"><span class="pl-c">     * Grid columns for tests</span></span></td>
      </tr>
      <tr>
        <td id="L21" class="blob-num js-line-number" data-line-number="21"></td>
        <td id="LC21" class="blob-code blob-code-inner js-file-line"><span class="pl-s1"><span class="pl-c">     *</span></span></td>
      </tr>
      <tr>
        <td id="L22" class="blob-num js-line-number" data-line-number="22"></td>
        <td id="LC22" class="blob-code blob-code-inner js-file-line"><span class="pl-s1"><span class="pl-c">     * <span class="pl-k">@var</span> array</span></span></td>
      </tr>
      <tr>
        <td id="L23" class="blob-num js-line-number" data-line-number="23"></td>
        <td id="LC23" class="blob-code blob-code-inner js-file-line"><span class="pl-s1"><span class="pl-c">     <span class="pl-c">*/</span></span></span></td>
      </tr>
      <tr>
        <td id="L24" class="blob-num js-line-number" data-line-number="24"></td>
        <td id="LC24" class="blob-code blob-code-inner js-file-line"><span class="pl-s1">    <span class="pl-k">private</span> <span class="pl-smi">$testFilterColumns</span> <span class="pl-k">=</span> [</span></td>
      </tr>
      <tr>
        <td id="L25" class="blob-num js-line-number" data-line-number="25"></td>
        <td id="LC25" class="blob-code blob-code-inner js-file-line"><span class="pl-s1">        <span class="pl-s"><span class="pl-pds">&#39;</span>visibility<span class="pl-pds">&#39;</span></span>,</span></td>
      </tr>
      <tr>
        <td id="L26" class="blob-num js-line-number" data-line-number="26"></td>
        <td id="LC26" class="blob-code blob-code-inner js-file-line"><span class="pl-s1">    ];</span></td>
      </tr>
      <tr>
        <td id="L27" class="blob-num js-line-number" data-line-number="27"></td>
        <td id="LC27" class="blob-code blob-code-inner js-file-line"><span class="pl-s1">    </span></td>
      </tr>
      <tr>
        <td id="L28" class="blob-num js-line-number" data-line-number="28"></td>
        <td id="LC28" class="blob-code blob-code-inner js-file-line"><span class="pl-s1">    <span class="pl-c"><span class="pl-c">/**</span></span></span></td>
      </tr>
      <tr>
        <td id="L29" class="blob-num js-line-number" data-line-number="29"></td>
        <td id="LC29" class="blob-code blob-code-inner js-file-line"><span class="pl-s1"><span class="pl-c">     * Assert that category products grid filter works correctly.</span></span></td>
      </tr>
      <tr>
        <td id="L30" class="blob-num js-line-number" data-line-number="30"></td>
        <td id="LC30" class="blob-code blob-code-inner js-file-line"><span class="pl-s1"><span class="pl-c">     *</span></span></td>
      </tr>
      <tr>
        <td id="L31" class="blob-num js-line-number" data-line-number="31"></td>
        <td id="LC31" class="blob-code blob-code-inner js-file-line"><span class="pl-s1"><span class="pl-c">     * <span class="pl-k">@param</span> CatalogCategoryIndex $catalogCategoryIndex</span></span></td>
      </tr>
      <tr>
        <td id="L32" class="blob-num js-line-number" data-line-number="32"></td>
        <td id="LC32" class="blob-code blob-code-inner js-file-line"><span class="pl-s1"><span class="pl-c">     * <span class="pl-k">@param</span> CatalogCategoryEdit $catalogCategoryEdit</span></span></td>
      </tr>
      <tr>
        <td id="L33" class="blob-num js-line-number" data-line-number="33"></td>
        <td id="LC33" class="blob-code blob-code-inner js-file-line"><span class="pl-s1"><span class="pl-c">     * <span class="pl-k">@param</span> Category $category</span></span></td>
      </tr>
      <tr>
        <td id="L34" class="blob-num js-line-number" data-line-number="34"></td>
        <td id="LC34" class="blob-code blob-code-inner js-file-line"><span class="pl-s1"><span class="pl-c">     * <span class="pl-k">@return</span> void</span></span></td>
      </tr>
      <tr>
        <td id="L35" class="blob-num js-line-number" data-line-number="35"></td>
        <td id="LC35" class="blob-code blob-code-inner js-file-line"><span class="pl-s1"><span class="pl-c">     <span class="pl-c">*/</span></span></span></td>
      </tr>
      <tr>
        <td id="L36" class="blob-num js-line-number" data-line-number="36"></td>
        <td id="LC36" class="blob-code blob-code-inner js-file-line"><span class="pl-s1">    <span class="pl-k">public</span> <span class="pl-k">function</span> <span class="pl-en">processAssert</span>(</span></td>
      </tr>
      <tr>
        <td id="L37" class="blob-num js-line-number" data-line-number="37"></td>
        <td id="LC37" class="blob-code blob-code-inner js-file-line"><span class="pl-s1">        <span class="pl-c1">CatalogCategoryIndex</span> <span class="pl-smi">$catalogCategoryIndex</span>,</span></td>
      </tr>
      <tr>
        <td id="L38" class="blob-num js-line-number" data-line-number="38"></td>
        <td id="LC38" class="blob-code blob-code-inner js-file-line"><span class="pl-s1">        <span class="pl-c1">CatalogCategoryEdit</span> <span class="pl-smi">$catalogCategoryEdit</span>,</span></td>
      </tr>
      <tr>
        <td id="L39" class="blob-num js-line-number" data-line-number="39"></td>
        <td id="LC39" class="blob-code blob-code-inner js-file-line"><span class="pl-s1">        <span class="pl-c1">Category</span> <span class="pl-smi">$category</span></span></td>
      </tr>
      <tr>
        <td id="L40" class="blob-num js-line-number" data-line-number="40"></td>
        <td id="LC40" class="blob-code blob-code-inner js-file-line"><span class="pl-s1">    ) {</span></td>
      </tr>
      <tr>
        <td id="L41" class="blob-num js-line-number" data-line-number="41"></td>
        <td id="LC41" class="blob-code blob-code-inner js-file-line"><span class="pl-s1">        <span class="pl-smi">$catalogCategoryIndex</span><span class="pl-k">-&gt;</span>getTreeCategories()<span class="pl-k">-&gt;</span>selectCategory(<span class="pl-smi">$category</span>, <span class="pl-c1">true</span>);</span></td>
      </tr>
      <tr>
        <td id="L42" class="blob-num js-line-number" data-line-number="42"></td>
        <td id="LC42" class="blob-code blob-code-inner js-file-line"><span class="pl-s1">        <span class="pl-smi">$categoryProducts</span> <span class="pl-k">=</span> <span class="pl-smi">$category</span><span class="pl-k">-&gt;</span>getDataFieldConfig(<span class="pl-s"><span class="pl-pds">&#39;</span>category_products<span class="pl-pds">&#39;</span></span>)[<span class="pl-s"><span class="pl-pds">&#39;</span>source<span class="pl-pds">&#39;</span></span>]<span class="pl-k">-&gt;</span>getProducts();</span></td>
      </tr>
      <tr>
        <td id="L43" class="blob-num js-line-number" data-line-number="43"></td>
        <td id="LC43" class="blob-code blob-code-inner js-file-line"><span class="pl-s1">        <span class="pl-smi">$catalogCategoryEdit</span><span class="pl-k">-&gt;</span>getEditForm()<span class="pl-k">-&gt;</span>openSection(<span class="pl-s"><span class="pl-pds">&#39;</span>category_products<span class="pl-pds">&#39;</span></span>);</span></td>
      </tr>
      <tr>
        <td id="L44" class="blob-num js-line-number" data-line-number="44"></td>
        <td id="LC44" class="blob-code blob-code-inner js-file-line"><span class="pl-s1">        </span></td>
      </tr>
      <tr>
        <td id="L45" class="blob-num js-line-number" data-line-number="45"></td>
        <td id="LC45" class="blob-code blob-code-inner js-file-line"><span class="pl-s1">        <span class="pl-k">foreach</span> (<span class="pl-smi">$this</span><span class="pl-k">-&gt;</span><span class="pl-smi">testFilterColumns</span> <span class="pl-k">as</span> <span class="pl-smi">$field</span>) {</span></td>
      </tr>
      <tr>
        <td id="L46" class="blob-num js-line-number" data-line-number="46"></td>
        <td id="LC46" class="blob-code blob-code-inner js-file-line"><span class="pl-s1">            <span class="pl-smi">$this</span><span class="pl-k">-&gt;</span>testGridFilter(<span class="pl-smi">$categoryProducts</span>, <span class="pl-smi">$catalogCategoryEdit</span>, <span class="pl-smi">$field</span>);</span></td>
      </tr>
      <tr>
        <td id="L47" class="blob-num js-line-number" data-line-number="47"></td>
        <td id="LC47" class="blob-code blob-code-inner js-file-line"><span class="pl-s1">        }</span></td>
      </tr>
      <tr>
        <td id="L48" class="blob-num js-line-number" data-line-number="48"></td>
        <td id="LC48" class="blob-code blob-code-inner js-file-line"><span class="pl-s1">    }</span></td>
      </tr>
      <tr>
        <td id="L49" class="blob-num js-line-number" data-line-number="49"></td>
        <td id="LC49" class="blob-code blob-code-inner js-file-line"><span class="pl-s1"></span></td>
      </tr>
      <tr>
        <td id="L50" class="blob-num js-line-number" data-line-number="50"></td>
        <td id="LC50" class="blob-code blob-code-inner js-file-line"><span class="pl-s1">    <span class="pl-c"><span class="pl-c">/**</span></span></span></td>
      </tr>
      <tr>
        <td id="L51" class="blob-num js-line-number" data-line-number="51"></td>
        <td id="LC51" class="blob-code blob-code-inner js-file-line"><span class="pl-s1"><span class="pl-c">     * <span class="pl-k">@param</span> array $categoryProducts</span></span></td>
      </tr>
      <tr>
        <td id="L52" class="blob-num js-line-number" data-line-number="52"></td>
        <td id="LC52" class="blob-code blob-code-inner js-file-line"><span class="pl-s1"><span class="pl-c">     * <span class="pl-k">@param</span> CatalogCategoryEdit $catalogCategoryEdit</span></span></td>
      </tr>
      <tr>
        <td id="L53" class="blob-num js-line-number" data-line-number="53"></td>
        <td id="LC53" class="blob-code blob-code-inner js-file-line"><span class="pl-s1"><span class="pl-c">     * <span class="pl-k">@param</span> string $filterField</span></span></td>
      </tr>
      <tr>
        <td id="L54" class="blob-num js-line-number" data-line-number="54"></td>
        <td id="LC54" class="blob-code blob-code-inner js-file-line"><span class="pl-s1"><span class="pl-c">     <span class="pl-c">*/</span></span></span></td>
      </tr>
      <tr>
        <td id="L55" class="blob-num js-line-number" data-line-number="55"></td>
        <td id="LC55" class="blob-code blob-code-inner js-file-line"><span class="pl-s1">    <span class="pl-k">private</span> <span class="pl-k">function</span> <span class="pl-en">testGridFilter</span>(<span class="pl-k">array</span> <span class="pl-smi">$categoryProducts</span>, <span class="pl-c1">CatalogCategoryEdit</span> <span class="pl-smi">$catalogCategoryEdit</span>, <span class="pl-smi">$filterField</span>)</span></td>
      </tr>
      <tr>
        <td id="L56" class="blob-num js-line-number" data-line-number="56"></td>
        <td id="LC56" class="blob-code blob-code-inner js-file-line"><span class="pl-s1">    {</span></td>
      </tr>
      <tr>
        <td id="L57" class="blob-num js-line-number" data-line-number="57"></td>
        <td id="LC57" class="blob-code blob-code-inner js-file-line"><span class="pl-s1">        <span class="pl-smi">$productsByFilter</span> <span class="pl-k">=</span> [];</span></td>
      </tr>
      <tr>
        <td id="L58" class="blob-num js-line-number" data-line-number="58"></td>
        <td id="LC58" class="blob-code blob-code-inner js-file-line"><span class="pl-s1">        <span class="pl-k">foreach</span> (<span class="pl-smi">$categoryProducts</span> <span class="pl-k">as</span> <span class="pl-smi">$product</span>) {</span></td>
      </tr>
      <tr>
        <td id="L59" class="blob-num js-line-number" data-line-number="59"></td>
        <td id="LC59" class="blob-code blob-code-inner js-file-line"><span class="pl-s1">            <span class="pl-smi">$filterValue</span> <span class="pl-k">=</span> <span class="pl-smi">$product</span><span class="pl-k">-&gt;</span>getData(<span class="pl-smi">$filterField</span>);</span></td>
      </tr>
      <tr>
        <td id="L60" class="blob-num js-line-number" data-line-number="60"></td>
        <td id="LC60" class="blob-code blob-code-inner js-file-line"><span class="pl-s1">            <span class="pl-k">if</span> (<span class="pl-k">!</span><span class="pl-c1">isset</span>(<span class="pl-smi">$productsByFilter</span>[<span class="pl-smi">$filterValue</span>])) {</span></td>
      </tr>
      <tr>
        <td id="L61" class="blob-num js-line-number" data-line-number="61"></td>
        <td id="LC61" class="blob-code blob-code-inner js-file-line"><span class="pl-s1">                <span class="pl-smi">$productsByFilter</span>[<span class="pl-smi">$filterValue</span>] <span class="pl-k">=</span> [];</span></td>
      </tr>
      <tr>
        <td id="L62" class="blob-num js-line-number" data-line-number="62"></td>
        <td id="LC62" class="blob-code blob-code-inner js-file-line"><span class="pl-s1">            }</span></td>
      </tr>
      <tr>
        <td id="L63" class="blob-num js-line-number" data-line-number="63"></td>
        <td id="LC63" class="blob-code blob-code-inner js-file-line"><span class="pl-s1">            <span class="pl-smi">$productsByFilter</span>[<span class="pl-smi">$filterValue</span>][] <span class="pl-k">=</span> <span class="pl-smi">$product</span>;</span></td>
      </tr>
      <tr>
        <td id="L64" class="blob-num js-line-number" data-line-number="64"></td>
        <td id="LC64" class="blob-code blob-code-inner js-file-line"><span class="pl-s1">        }</span></td>
      </tr>
      <tr>
        <td id="L65" class="blob-num js-line-number" data-line-number="65"></td>
        <td id="LC65" class="blob-code blob-code-inner js-file-line"><span class="pl-s1"></span></td>
      </tr>
      <tr>
        <td id="L66" class="blob-num js-line-number" data-line-number="66"></td>
        <td id="LC66" class="blob-code blob-code-inner js-file-line"><span class="pl-s1">        <span class="pl-smi">$productsFieldset</span> <span class="pl-k">=</span> <span class="pl-smi">$catalogCategoryEdit</span><span class="pl-k">-&gt;</span>getEditForm()<span class="pl-k">-&gt;</span>getSection(<span class="pl-s"><span class="pl-pds">&#39;</span>category_products<span class="pl-pds">&#39;</span></span>);</span></td>
      </tr>
      <tr>
        <td id="L67" class="blob-num js-line-number" data-line-number="67"></td>
        <td id="LC67" class="blob-code blob-code-inner js-file-line"><span class="pl-s1">        <span class="pl-k">foreach</span> (<span class="pl-smi">$productsByFilter</span> <span class="pl-k">as</span> <span class="pl-smi">$filterValue</span> <span class="pl-k">=&gt;</span> <span class="pl-smi">$products</span>) {</span></td>
      </tr>
      <tr>
        <td id="L68" class="blob-num js-line-number" data-line-number="68"></td>
        <td id="LC68" class="blob-code blob-code-inner js-file-line"><span class="pl-s1">            <span class="pl-smi">$productsFieldset</span><span class="pl-k">-&gt;</span>getProductGrid()<span class="pl-k">-&gt;</span>search([</span></td>
      </tr>
      <tr>
        <td id="L69" class="blob-num js-line-number" data-line-number="69"></td>
        <td id="LC69" class="blob-code blob-code-inner js-file-line"><span class="pl-s1">                <span class="pl-s"><span class="pl-pds">&#39;</span>in_category<span class="pl-pds">&#39;</span></span> <span class="pl-k">=&gt;</span> <span class="pl-s"><span class="pl-pds">&#39;</span>Yes<span class="pl-pds">&#39;</span></span>,</span></td>
      </tr>
      <tr>
        <td id="L70" class="blob-num js-line-number" data-line-number="70"></td>
        <td id="LC70" class="blob-code blob-code-inner js-file-line"><span class="pl-s1">                <span class="pl-smi">$filterField</span> <span class="pl-k">=&gt;</span> <span class="pl-smi">$filterValue</span>,</span></td>
      </tr>
      <tr>
        <td id="L71" class="blob-num js-line-number" data-line-number="71"></td>
        <td id="LC71" class="blob-code blob-code-inner js-file-line"><span class="pl-s1">            ]);</span></td>
      </tr>
      <tr>
        <td id="L72" class="blob-num js-line-number" data-line-number="72"></td>
        <td id="LC72" class="blob-code blob-code-inner js-file-line"><span class="pl-s1"></span></td>
      </tr>
      <tr>
        <td id="L73" class="blob-num js-line-number" data-line-number="73"></td>
        <td id="LC73" class="blob-code blob-code-inner js-file-line"><span class="pl-s1">            <span class="pl-smi">$expectedRows</span> <span class="pl-k">=</span> [];</span></td>
      </tr>
      <tr>
        <td id="L74" class="blob-num js-line-number" data-line-number="74"></td>
        <td id="LC74" class="blob-code blob-code-inner js-file-line"><span class="pl-s1">            <span class="pl-k">foreach</span> (<span class="pl-smi">$products</span> <span class="pl-k">as</span> <span class="pl-smi">$product</span>) {</span></td>
      </tr>
      <tr>
        <td id="L75" class="blob-num js-line-number" data-line-number="75"></td>
        <td id="LC75" class="blob-code blob-code-inner js-file-line"><span class="pl-s1">                <span class="pl-smi">$expectedRows</span>[] <span class="pl-k">=</span> <span class="pl-smi">$product</span><span class="pl-k">-&gt;</span>getName();</span></td>
      </tr>
      <tr>
        <td id="L76" class="blob-num js-line-number" data-line-number="76"></td>
        <td id="LC76" class="blob-code blob-code-inner js-file-line"><span class="pl-s1">            }</span></td>
      </tr>
      <tr>
        <td id="L77" class="blob-num js-line-number" data-line-number="77"></td>
        <td id="LC77" class="blob-code blob-code-inner js-file-line"><span class="pl-s1">            <span class="pl-smi">$gridRows</span> <span class="pl-k">=</span> <span class="pl-smi">$productsFieldset</span><span class="pl-k">-&gt;</span>getProductGrid()<span class="pl-k">-&gt;</span>getRowsData([<span class="pl-s"><span class="pl-pds">&#39;</span>name<span class="pl-pds">&#39;</span></span>]);</span></td>
      </tr>
      <tr>
        <td id="L78" class="blob-num js-line-number" data-line-number="78"></td>
        <td id="LC78" class="blob-code blob-code-inner js-file-line"><span class="pl-s1">            <span class="pl-smi">$actualRows</span> <span class="pl-k">=</span> <span class="pl-c1">array_column</span>(<span class="pl-smi">$gridRows</span>, <span class="pl-s"><span class="pl-pds">&#39;</span>name<span class="pl-pds">&#39;</span></span>);</span></td>
      </tr>
      <tr>
        <td id="L79" class="blob-num js-line-number" data-line-number="79"></td>
        <td id="LC79" class="blob-code blob-code-inner js-file-line"><span class="pl-s1">            <span class="pl-c1">sort</span>(<span class="pl-smi">$expectedRows</span>);</span></td>
      </tr>
      <tr>
        <td id="L80" class="blob-num js-line-number" data-line-number="80"></td>
        <td id="LC80" class="blob-code blob-code-inner js-file-line"><span class="pl-s1">            <span class="pl-c1">sort</span>(<span class="pl-smi">$actualRows</span>);</span></td>
      </tr>
      <tr>
        <td id="L81" class="blob-num js-line-number" data-line-number="81"></td>
        <td id="LC81" class="blob-code blob-code-inner js-file-line"><span class="pl-s1"></span></td>
      </tr>
      <tr>
        <td id="L82" class="blob-num js-line-number" data-line-number="82"></td>
        <td id="LC82" class="blob-code blob-code-inner js-file-line"><span class="pl-s1">            <span class="pl-c1">\</span><span class="pl-c1">PHPUnit_Framework_Assert</span><span class="pl-k">::</span>assertEquals(</span></td>
      </tr>
      <tr>
        <td id="L83" class="blob-num js-line-number" data-line-number="83"></td>
        <td id="LC83" class="blob-code blob-code-inner js-file-line"><span class="pl-s1">                <span class="pl-smi">$expectedRows</span>,</span></td>
      </tr>
      <tr>
        <td id="L84" class="blob-num js-line-number" data-line-number="84"></td>
        <td id="LC84" class="blob-code blob-code-inner js-file-line"><span class="pl-s1">                <span class="pl-smi">$actualRows</span>,</span></td>
      </tr>
      <tr>
        <td id="L85" class="blob-num js-line-number" data-line-number="85"></td>
        <td id="LC85" class="blob-code blob-code-inner js-file-line"><span class="pl-s1">                <span class="pl-s"><span class="pl-pds">&quot;</span>Category products grid filter &#39;<span class="pl-smi">$filterField</span>&#39; does not work correctly<span class="pl-pds">&quot;</span></span></span></td>
      </tr>
      <tr>
        <td id="L86" class="blob-num js-line-number" data-line-number="86"></td>
        <td id="LC86" class="blob-code blob-code-inner js-file-line"><span class="pl-s1">            );</span></td>
      </tr>
      <tr>
        <td id="L87" class="blob-num js-line-number" data-line-number="87"></td>
        <td id="LC87" class="blob-code blob-code-inner js-file-line"><span class="pl-s1">        }</span></td>
      </tr>
      <tr>
        <td id="L88" class="blob-num js-line-number" data-line-number="88"></td>
        <td id="LC88" class="blob-code blob-code-inner js-file-line"><span class="pl-s1">    }</span></td>
      </tr>
      <tr>
        <td id="L89" class="blob-num js-line-number" data-line-number="89"></td>
        <td id="LC89" class="blob-code blob-code-inner js-file-line"><span class="pl-s1"></span></td>
      </tr>
      <tr>
        <td id="L90" class="blob-num js-line-number" data-line-number="90"></td>
        <td id="LC90" class="blob-code blob-code-inner js-file-line"><span class="pl-s1">    <span class="pl-c"><span class="pl-c">/**</span></span></span></td>
      </tr>
      <tr>
        <td id="L91" class="blob-num js-line-number" data-line-number="91"></td>
        <td id="LC91" class="blob-code blob-code-inner js-file-line"><span class="pl-s1"><span class="pl-c">     * Returns a string representation of the object.</span></span></td>
      </tr>
      <tr>
        <td id="L92" class="blob-num js-line-number" data-line-number="92"></td>
        <td id="LC92" class="blob-code blob-code-inner js-file-line"><span class="pl-s1"><span class="pl-c">     *</span></span></td>
      </tr>
      <tr>
        <td id="L93" class="blob-num js-line-number" data-line-number="93"></td>
        <td id="LC93" class="blob-code blob-code-inner js-file-line"><span class="pl-s1"><span class="pl-c">     * <span class="pl-k">@return</span> string</span></span></td>
      </tr>
      <tr>
        <td id="L94" class="blob-num js-line-number" data-line-number="94"></td>
        <td id="LC94" class="blob-code blob-code-inner js-file-line"><span class="pl-s1"><span class="pl-c">     <span class="pl-c">*/</span></span></span></td>
      </tr>
      <tr>
        <td id="L95" class="blob-num js-line-number" data-line-number="95"></td>
        <td id="LC95" class="blob-code blob-code-inner js-file-line"><span class="pl-s1">    <span class="pl-k">public</span> <span class="pl-k">function</span> <span class="pl-en">toString</span>()</span></td>
      </tr>
      <tr>
        <td id="L96" class="blob-num js-line-number" data-line-number="96"></td>
        <td id="LC96" class="blob-code blob-code-inner js-file-line"><span class="pl-s1">    {</span></td>
      </tr>
      <tr>
        <td id="L97" class="blob-num js-line-number" data-line-number="97"></td>
        <td id="LC97" class="blob-code blob-code-inner js-file-line"><span class="pl-s1">        <span class="pl-k">return</span> <span class="pl-s"><span class="pl-pds">&#39;</span>Category products grid filter works correctly<span class="pl-pds">&#39;</span></span>;</span></td>
      </tr>
      <tr>
        <td id="L98" class="blob-num js-line-number" data-line-number="98"></td>
        <td id="LC98" class="blob-code blob-code-inner js-file-line"><span class="pl-s1">    }</span></td>
      </tr>
      <tr>
        <td id="L99" class="blob-num js-line-number" data-line-number="99"></td>
        <td id="LC99" class="blob-code blob-code-inner js-file-line"><span class="pl-s1">}</span></td>
      </tr>
</table>

  <div class="BlobToolbar position-absolute js-file-line-actions dropdown js-menu-container js-select-menu d-none" aria-hidden="true">
    <button class="btn-octicon ml-0 px-2 p-0 bg-white border border-gray-dark rounded-1 dropdown-toggle js-menu-target" type="button" aria-expanded="false" aria-haspopup="true" aria-label="Inline file action toolbar" aria-controls="inline-file-actions">
      <svg class="octicon octicon-kebab-horizontal" viewBox="0 0 13 16" version="1.1" width="13" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M1.5 9a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3zm5 0a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3zM13 7.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0z"/></svg>
    </button>
    <div class="dropdown-menu-content js-menu-content" id="inline-file-actions">
      <ul class="BlobToolbar-dropdown dropdown-menu dropdown-menu-se mt-2">
        <li><clipboard-copy class="dropdown-item" id="js-copy-lines" style="cursor:pointer;" data-original-text="Copy lines">Copy lines</clipboard-copy></li>
        <li><clipboard-copy class="dropdown-item" id="js-copy-permalink" style="cursor:pointer;" data-original-text="Copy permalink">Copy permalink</clipboard-copy></li>
        <li><a class="dropdown-item js-update-url-with-hash" id="js-view-git-blame" href="/magento/magento2/blame/578e62cf9753b5e74d02f4df36da5865738600f6/dev/tests/functional/tests/app/Magento/Catalog/Test/Constraint/AssertCategoryProductsGridFilter.php">View git blame</a></li>
          <li><a class="dropdown-item" id="js-new-issue" href="/magento/magento2/issues/new">Open new issue</a></li>
      </ul>
    </div>
  </div>

  </div>

  </div>

  <button type="button" data-facebox="#jump-to-line" data-facebox-class="linejump" data-hotkey="l" class="d-none">Jump to Line</button>
  <div id="jump-to-line" style="display:none">
    <!-- '"` --><!-- </textarea></xmp> --></option></form><form class="js-jump-to-line-form" action="" accept-charset="UTF-8" method="get"><input name="utf8" type="hidden" value="&#x2713;" />
      <input class="form-control linejump-input js-jump-to-line-field" type="text" placeholder="Jump to line&hellip;" aria-label="Jump to line" autofocus>
      <button type="submit" class="btn">Go</button>
</form>  </div>


  </div>
  <div class="modal-backdrop js-touch-events"></div>
</div>

    </div>
  </div>

  </div>

      
<div class="footer container-lg px-3" role="contentinfo">
  <div class="position-relative d-flex flex-justify-between pt-6 pb-2 mt-6 f6 text-gray border-top border-gray-light ">
    <ul class="list-style-none d-flex flex-wrap ">
      <li class="mr-3">&copy; 2018 <span title="0.64977s from unicorn-2986557156-kskdw">GitHub</span>, Inc.</li>
        <li class="mr-3"><a data-ga-click="Footer, go to terms, text:terms" href="https://github.com/site/terms">Terms</a></li>
        <li class="mr-3"><a data-ga-click="Footer, go to privacy, text:privacy" href="https://github.com/site/privacy">Privacy</a></li>
        <li class="mr-3"><a href="https://help.github.com/articles/github-security/" data-ga-click="Footer, go to security, text:security">Security</a></li>
        <li class="mr-3"><a href="https://status.github.com/" data-ga-click="Footer, go to status, text:status">Status</a></li>
        <li><a data-ga-click="Footer, go to help, text:help" href="https://help.github.com">Help</a></li>
    </ul>

    <a aria-label="Homepage" title="GitHub" class="footer-octicon" href="https://github.com">
      <svg height="24" class="octicon octicon-mark-github" viewBox="0 0 16 16" version="1.1" width="24" aria-hidden="true"><path fill-rule="evenodd" d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.013 8.013 0 0 0 16 8c0-4.42-3.58-8-8-8z"/></svg>
</a>
   <ul class="list-style-none d-flex flex-wrap ">
        <li class="mr-3"><a data-ga-click="Footer, go to contact, text:contact" href="https://github.com/contact">Contact GitHub</a></li>
      <li class="mr-3"><a href="https://developer.github.com" data-ga-click="Footer, go to api, text:api">API</a></li>
      <li class="mr-3"><a href="https://training.github.com" data-ga-click="Footer, go to training, text:training">Training</a></li>
      <li class="mr-3"><a href="https://shop.github.com" data-ga-click="Footer, go to shop, text:shop">Shop</a></li>
        <li class="mr-3"><a href="https://blog.github.com" data-ga-click="Footer, go to blog, text:blog">Blog</a></li>
        <li><a data-ga-click="Footer, go to about, text:about" href="https://github.com/about">About</a></li>

    </ul>
  </div>
  <div class="d-flex flex-justify-center pb-6">
    <span class="f6 text-gray-light"></span>
  </div>
</div>



  <div id="ajax-error-message" class="ajax-error-message flash flash-error">
    <svg class="octicon octicon-alert" viewBox="0 0 16 16" version="1.1" width="16" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M8.893 1.5c-.183-.31-.52-.5-.887-.5s-.703.19-.886.5L.138 13.499a.98.98 0 0 0 0 1.001c.193.31.53.501.886.501h13.964c.367 0 .704-.19.877-.5a1.03 1.03 0 0 0 .01-1.002L8.893 1.5zm.133 11.497H6.987v-2.003h2.039v2.003zm0-3.004H6.987V5.987h2.039v4.006z"/></svg>
    <button type="button" class="flash-close js-ajax-error-dismiss" aria-label="Dismiss error">
      <svg class="octicon octicon-x" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M7.48 8l3.75 3.75-1.48 1.48L6 9.48l-3.75 3.75-1.48-1.48L4.52 8 .77 4.25l1.48-1.48L6 6.52l3.75-3.75 1.48 1.48L7.48 8z"/></svg>
    </button>
    You can’t perform that action at this time.
  </div>


    <script crossorigin="anonymous" integrity="sha512-2GVr5rsbbfKbHM6oRrri41+qJ2ltJBCqluASS29fj+9yHGLFmFhq0C64VMdL57UJ34G2+FXU+8FZhaAOnsCEhw==" type="application/javascript" src="https://assets-cdn.github.com/assets/compat-bb7abfb15ed4ffb0da9056d4c980fba5.js"></script>
    <script crossorigin="anonymous" integrity="sha512-HD3VGNUZdKcyhzxthfJME6V4ByRTPdCGqlqq7sPm8jFweQQjz00nAJzWMAR9GUDiP2yYWqSxM6JxNxc2J/4rGQ==" type="application/javascript" src="https://assets-cdn.github.com/assets/frameworks-2afbd5df19ed1f9aaf0a04c15e9d0b35.js"></script>
    
    <script crossorigin="anonymous" async="async" integrity="sha512-IdHQYUq4tkFFl+J/TM87/2TTheiMcB5bJfeRbGRfac3jQMr9DHMNsiuFC/1Vm+tTh3OwkssVx5Tk0iZIpfbZuQ==" type="application/javascript" src="https://assets-cdn.github.com/assets/github-d67ce02b55f439df4644fda3f6efab0a.js"></script>
    
    
    
  <div class="js-stale-session-flash stale-session-flash flash flash-warn flash-banner d-none">
    <svg class="octicon octicon-alert" viewBox="0 0 16 16" version="1.1" width="16" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M8.893 1.5c-.183-.31-.52-.5-.887-.5s-.703.19-.886.5L.138 13.499a.98.98 0 0 0 0 1.001c.193.31.53.501.886.501h13.964c.367 0 .704-.19.877-.5a1.03 1.03 0 0 0 .01-1.002L8.893 1.5zm.133 11.497H6.987v-2.003h2.039v2.003zm0-3.004H6.987V5.987h2.039v4.006z"/></svg>
    <span class="signed-in-tab-flash">You signed in with another tab or window. <a href="">Reload</a> to refresh your session.</span>
    <span class="signed-out-tab-flash">You signed out in another tab or window. <a href="">Reload</a> to refresh your session.</span>
  </div>
  <div class="facebox" id="facebox" style="display:none;">
  <div class="facebox-popup">
    <div class="facebox-content" role="dialog" aria-labelledby="facebox-header" aria-describedby="facebox-description">
    </div>
    <button type="button" class="facebox-close js-facebox-close" aria-label="Close modal">
      <svg class="octicon octicon-x" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M7.48 8l3.75 3.75-1.48 1.48L6 9.48l-3.75 3.75-1.48-1.48L4.52 8 .77 4.25l1.48-1.48L6 6.52l3.75-3.75 1.48 1.48L7.48 8z"/></svg>
    </button>
  </div>
</div>

  <div class="Popover js-hovercard-content position-absolute" style="display: none; outline: none;" tabindex="0">
  <div class="Popover-message Popover-message--bottom-left Popover-message--large Box box-shadow-large" style="width:360px;">
  </div>
</div>

<div id="hovercard-aria-description" class="sr-only">
  Press h to open a hovercard with more details.
</div>


  </body>
</html>

