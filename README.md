# harvest2gitlab
### A simple tool to import your Harvest time entries to Gitlab

## Setup
1. Add addon to your Firefox or chrome browser:
    - Firefox: https://addons.mozilla.org/de/firefox/addon/git-harvest/
    - Chrome: https://chrome.google.com/webstore/detail/git-harvest/ofbfhaiknhlanbliolbkiidknakaldmo
2. Go to the addon settings, press "Add host' and add your git url
3. Click on the gray Harvest icon on the bottom left of your Gitlab ticket to start tracking
4. Execute installation


---------------------------------
##Using Docksal

### Installation
1. Execute "fin init"
2. Enter the necessary data as requested

### How to import
1. Type "fin sync" and your harvest project id or project code into your command line to get the import started
   -> e.g. fin sync HARVEST_PROJECT_ID HARVEST_PROJECT_CODE


---------------------------------
##Without Docksal

### Installation
1. Composer install
2. Add .env.local to your root directory
3. Copy text and replace placeholder with your data
 - HARVEST_ACCOUNT_ID="your_harvest_account_id"
 - HARVEST_ACCESS_TOKEN="your_harvest_access_token"
 - GIT_URL="your_git_url"
 - GIT_TOKEN="your_git_token"

### How to Import
Command: php bin/console app:harvest:time-export harvest_project_id harvest_project_code