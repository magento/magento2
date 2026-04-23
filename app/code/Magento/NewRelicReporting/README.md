# Magento_NewRelicReporting module

This module implements integration New Relic APM and New Relic Insights with Magento, giving real-time visibility into business and performance metrics for data-driven decision making.

## Installation

Before installing this module, note that the Magento_NewRelicReporting is dependent on the following modules:

- `Magento_Store`
- `Magento_Customer`
- `Magento_Backend`
- `Magento_Catalog`
- `Magento_ConfigurableProduct`
- `Magento_Config`

This module creates the following tables in the database:

- `reporting_counts`
- `reporting_module_status`
- `reporting_orders`
- `reporting_users`
- `reporting_system_updates`

For information about a module installation, see [Enable or disable modules](https://experienceleague.adobe.com/en/docs/commerce-operations/installation-guide/tutorials/manage-modules).

## Extensibility

Extension developers can interact with the Magento_NewRelicReporting module. For more information about the extension mechanism, see [Plugins](https://developer.adobe.com/commerce/php/development/components/plugins/).

[The dependency injection mechanism](https://developer.adobe.com/commerce/php/development/components/dependency-injection/) enables you to override the functionality of the Magento_NewRelicReporting module.

## Additional information

[Learn more about New Relic Reporting](https://experienceleague.adobe.com/en/docs/commerce-operations/tools/cli-reference/commerce-on-premises#newreliccreatedeploy-marker).

### Console commands

**Syntax:**
```bash
bin/magento newrelic:create:deploy-marker <message> [<changelog>] [<user>] [<revision>] [options]
```

**Arguments:**

- `<message>` - Required: Deployment description or title
- `[<changelog>]` - Optional: Summary of changes in this deployment
- `[<user>]` - Optional: User who performed the deployment (defaults to system user)
- `[<revision>]` - Optional: Version or revision identifier

**Options (NerdGraph enhanced):**

- `--commit="<hash>"` - Git commit hash for this deployment
- `--deep-link="<url>"` - Deep link to deployment details
- `--group-id="<id>"` - Group ID for organizing deployments

**Examples:**

Basic usage (works with both APIs):

```bash
bin/magento newrelic:create:deploy-marker "Release v1.2.0" "Bug fixes and performance improvements"
```

With user and revision:

```bash
bin/magento newrelic:create:deploy-marker "Release v1.2.0" "Bug fixes and performance improvements" "dev-team" "v1.2.0"
```

Enhanced usage with NerdGraph options:

```bash
bin/magento newrelic:create:deploy-marker "Production Deploy" "Updates and new features" "ops-user" "v1.2.0" \
  --commit="abc123def456" \
  --deep-link="https://github.com/<company>/<project>/releases/tag/v1.2.0" \
  --group-id="production"
```


[Learn more about command's parameters](https://experienceleague.adobe.com/en/docs/commerce-operations/tools/cli-reference/commerce-on-premises#newreliccreatedeploy-marker).

### Configuration

The module supports both v2 REST API and the modern NerdGraph GraphQL API for deployment tracking.

#### Admin configuration

Navigate to Stores > Configuration > General > New Relic Reporting, enable New Relic Integration, and select your Deployment API Mode (v2_rest for legacy REST or nerdgraph for modern GraphQL).

#### NerdGraph configuration (recommended)

When **Deployment API Mode** is set to **nerdgraph**, the following options are available:

- **New Relic API URL (NerdGraph)**:
    - US: `https://api.newrelic.com/graphql`
    - EU: `https://api.eu.newrelic.com/graphql`
- **Entity GUID (NerdGraph)**: Your application's entity GUID
- **New Relic API Key**: Create a user key, see the [New Relic API Keys](https://docs.newrelic.com/docs/apis/intro-apis/new-relic-api-keys/) documentation

#### V2 REST configuration

When **Deployment API Mode** is set to **v2_rest**, configure:

- **New Relic API URL (v2 REST)**: API endpoint
- **New Relic Application ID**: Can be found in the APM URL after "/applications/"
- **New Relic API Key**: Your REST API key

### NerdGraph features

When using NerdGraph mode, the module provides:

#### Enhanced Metadata Support

- **Commit hash**: Git commit tracking
- **Deep links**: Links to deployment details
- **Group ID**: Environment/team organization
- **Automatic timestamps**: Precise deployment timing
- **Version tracking**: Automatic or manual version assignment

### Cron options

Cron group configuration can be set at `etc/crontab.xml`:

- `magento_newrelicreporting_cron` - runs collecting all new relic reports

[Learn how to configure and run cron](https://experienceleague.adobe.com/en/docs/commerce-operations/configuration-guide/cli/configure-cron-jobs).
