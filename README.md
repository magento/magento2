[![Build Status](https://travis-ci.org/magento/magento2.svg?branch=2.1-develop)](https://travis-ci.org/magento/magento2)
[![Open Source Helpers](https://www.codetriage.com/magento/magento2/badges/users.svg)](https://www.codetriage.com/magento/magento2)
[![Gitter](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/magento/magento2?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge)
<h2>Welcome</h2>
Welcome to Magento 2 installation! We're glad you chose to install Magento 2, a cutting-edge, feature-rich eCommerce solution that gets results.

The installation instructions that used to be here are now published on our GitHub site. Use the information on this page to get started or go directly to the <a href="http://devdocs.magento.com/guides/v2.0/install-gde/bk-install-guide.html" target="_blank">guide</a>.

<h2>New to Magento? Need some help?</h2>
If you're not sure about the following, you probably need a little help before you start installing the Magento software:

*	Is the Magento software <a href="http://devdocs.magento.com/guides/v2.0/install-gde/basics/basics_magento-installed.html">installed already</a>?
*	What's a <a href="http://devdocs.magento.com/guides/v2.0/install-gde/basics/basics_login.html">terminal, command prompt, or Secure Shell (ssh)</a>?
*	Where's my <a href="http://devdocs.magento.com/guides/v2.0/install-gde/basics/basics_login.html">Magento server</a> and how do I access it?
*	What's <a href="http://devdocs.magento.com/guides/v2.0/install-gde/basics/basics_software.html">PHP</a>?
*	What's <a href="http://devdocs.magento.com/guides/v2.0/install-gde/basics/basics_software.html">Apache</a>?
*	What's <a href="http://devdocs.magento.com/guides/v2.0/install-gde/basics/basics_software.html">MySQL</a>?

<h2>Step 1: Verify your prerequisites</h2>

Use the following table to verify you have the correct prerequisites to install the Magento software.

<table>
	<tbody>
		<tr>
			<th>Prerequisite</th>
			<th>How to check</th>
			<th>For more information</th>
		</tr>
	<tr>
		<td>Apache 2.2 or 2.4</td>
		<td>Ubuntu: <code>apache2 -v</code><br>
		CentOS: <code>httpd -v</code></td>
		<td><a href="http://devdocs.magento.com/guides/v2.0/install-gde/prereq/apache.html">Apache</a></td>
	</tr>
	<tr>
		<td>PHP 5.6.x, 7.0.2, 7.0.4 or 7.0.6</td>
		<td><code>php -v</code></td>
		<td><a href="http://devdocs.magento.com/guides/v2.0/install-gde/prereq/php-ubuntu.html">PHP Ubuntu</a><br><a href="http://devdocs.magento.com/guides/v2.0/install-gde/prereq/php-centos.html">PHP CentOS</a></td>
	</tr>
	<tr><td>MySQL 5.6.x</td>
	<td><code>mysql -u [root user name] -p</code></td>
	<td><a href="http://devdocs.magento.com/guides/v2.0/install-gde/prereq/mysql.html">MySQL</a></td>
	</tr>
</tbody>
</table>

<h2>Step 2: Prepare to install</h2>

After verifying your prerequisites, perform the following tasks in order to prepare to install the Magento software.

1.	<a href="http://devdocs.magento.com/guides/v2.0/install-gde/install/composer-clone.html#instgde-prereq-compose-install">Install Composer</a>
2.	<a href="http://devdocs.magento.com/guides/v2.0/install-gde/install/composer-clone.html#instgde-prereq-compose-clone">Clone the Magento repository</a>

<h2>Step 3: Install and verify the installation</h2>

1.	<a href="http://devdocs.magento.com/guides/v2.0/install-gde/install/prepare-install.html">Update installation dependencies</a>
2.	Install Magento:
	*	<a href="http://devdocs.magento.com/guides/v2.0/install-gde/install/install-web.html">Install Magento software using the web interface</a>
	*	<a href="http://devdocs.magento.com/guides/v2.0/install-gde/install/install-cli.html">Install Magento software using the command line</a>
2.	<a href="http://devdocs.magento.com/guides/v2.0/install-gde/install/verify.html">Verify the installation</a>

<h2>Contributing to the Magento 2 code base</h2>
Contributions can take the form of new components or features, changes to existing features, tests, documentation (such as developer guides, user guides, examples, or specifications), bug fixes, optimizations, or just good suggestions.

To learn about how to make a contribution, click [here][1].

To learn about issues, click [here][2]. To open an issue, click [here][3].

To suggest documentation improvements, click [here][4].

[1]: <http://devdocs.magento.com/guides/v2.0/contributor-guide/contributing.html>
[2]: <http://devdocs.magento.com/guides/v2.0/contributor-guide/contributing.html#report>
[3]: <https://github.com/magento/magento2/issues>
[4]: <http://devdocs.magento.com>

<h3>Community Maintainers</h3>
The members of this team have been recognized for their outstanding commitment to maintaining and improving Magento. Magento has granted them permission to accept, merge, and reject pull requests, as well as review issues, and thanks these Community Maintainers for their valuable contributions.

<a href="https://magento.com/magento-contributors#maintainers">
    <img src="https://raw.githubusercontent.com/wiki/magento/magento2/images/maintainers.png"/>
</a>

<h3>Top Contributors</h3>
Magento is thankful for any contribution that can improve our code base, documentation or increase test coverage. We always recognize our most active members, as their contributions are the foundation of the Magento Open Source platform.
<a href="https://magento.com/magento-contributors">
    <img src="https://raw.githubusercontent.com/wiki/magento/magento2/images/contributors.png"/>
</a>

<h2>Reporting security issues</h2>

To report security vulnerabilities in Magento software or web sites, please create a Bugcrowd researcher account <a href="https://bugcrowd.com/magento">there</a> to submit and follow-up your issue. Learn more about reporting security issues <a href="https://magento.com/security/reporting-magento-security-issue">here</a>.

Stay up-to-date on the latest vulnerabilities and patches for Magento by signing up for <a href="https://magento.com/security/sign-up">Security Alert Notifications</a>.

