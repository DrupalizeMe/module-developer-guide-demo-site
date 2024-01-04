# Drupalize.Me Module Developer Guide Development Environment

Uses DDEV.

This contains a Drupal 10 project, and the custom code and configuration used while drafting the content of the Drupalize.Me Module Developer Guide.

Right now this contains the final working code, and final working example site.

This will give you a site built by following the User Guide scenario, and then going through the task tutorials in the module developer guide.

```bash
ddev start
ddev composer install
ddev import-db --file=backups/d10.final.sql.gz
```

If you want just the results of finishing the user guide, but before starting on the module developer guide use:

```bash
ddev import-db --file=backups/d10.start-here.sql.gz
```
