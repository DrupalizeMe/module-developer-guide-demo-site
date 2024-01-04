#!/bin/bash
# Copied from https://github.com/ddev/ddev-gitpod-launcher
set -eu

export DDEV_REPO="https://github.com/DrupalizeMe/module-developer-guide-demo-site"
echo "Checking out repository ${DDEV_REPO}"

ddev start
ddev composer install

# This won't be required in ddev v1.18.2+
printf "host_webserver_port: 8080\nhost_https_port: 2222\nhost_db_port: 3306\nhost_mailhog_port: 8025\nhost_phpmyadmin_port: 8036\nbind_all_interfaces: true\n" >.ddev/config.gitpod.yaml

ddev stop -a
ddev start -y

# Import artifacts.
if [[ ! -f ".site-data-loaded.txt" ]]; then
  echo "Importing backups. Only done on intial load."
  ddev import-db --file=backups/d10.start-here.sql.gz
  echo "Database backup imported from backups/d10.start-here.sql.gz"
  ddev import-files --source=backups/public_files.tar.gz
  echo "User files backup imported from backups/public_files.tar.gz"
  echo "Created by .gitpod/start_repo.sh" > .site-data-loaded.txt
fi

gp ports await 8080 && sleep 1 && gp preview $(gp url 8080)
