# Drupalize.Me Module Developer Guide Development Environment

This repository is intended to be used as a starting point for following along with the Drupalize.Me [Drupal Module Developer Guide](https://drupalize.me/guide/drupal-module-developer-guide). It contains the DDEV based Drupal 10 local development environment used in the Set up Your Development Environment tutorial.

It is possible to use any development environment of your choice to follow along with the guide. The primary things this repository contains are:

- A _composer.json_ file that contains the current version of the Drupal and the module's installed in the Drupal User Guide.
- A backup, in _backups/_ of the database, and files, from the Anytown Farmer's Market site created in the Drupal User Guide.

## Follow along with the guide

**Branch**: `main`

This project uses [DDEV](https://ddev.com/). Following the steps below will give you a site that resembles what you would have built by following the Drupal User Guide scenario.

Set up a cloud based environment and follow along using GitPod.io + DDEV.

[![Open in Gitpod](https://gitpod.io/button/open-in-gitpod.svg)](https://gitpod.io/#https://github.com/DrupalizeMe/module-developer-guide-demo-site)

Or, to run this locally, clone the repo and then run the following commands:

```bash
ddev start
ddev composer install
ddev import-db --file=backups/d10.start-here.sql.gz
ddev import-files --source=backups/public_files.tar.gz
ddev drush updatedb
ddev drush cr
```

If you want just the results of finishing the user guide, but before starting on the module developer guide use:

```bash
```

## Final code

**Branch**: `complete`

This branch contains the results of going through the all the task tutorials in the module developer guide. It is primarily used as a reference when working on the guide's content. But can also be used to check your work.

```bash
ddev start
ddev composer install
ddev import-db --file=backups/d10.final.sql.gz
ddev import-db --file=backups/d10.start-here.sql.gz
ddev import-files --source=backups/public_files.tar.gz
ddev drush updatedb
ddev drush cr
```
