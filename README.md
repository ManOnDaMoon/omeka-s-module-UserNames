# omeka-s-module-UserNames
Module for Omeka S

This module adds user name capability to Omeka S.
Users or administrators can setup user names in the Users configuration panel. Users can then use this as a login credential just like they already do with their email.

## Installing / Getting started

Starting v1.2, UserNames requires Omeka S v4.

* Download and unzip in your `omeka-s/modules` directory.
* Rename the uncompressed folder to `UserNames`.
* Log into your Omeka S admin backend and navigate to the `Modules` menu.
* Click `Install` next to the UserNames module.

## Features

This module includes the following features:

* Associate a user name your users' accounts on an Omeka S install. These user names are unique to each user.
* Log in using either the user email, as usual, or using the configured user name.
* User names are distinct from display names.
* Full compatibility with RestrictedSites module: use usernames even on your public sites that require authentification, and get user activation emails containing the defined username.
* Built-in EN and FR localization

### Set-up a user name

To configure a new user name, got to your admin dashboard and to the Users admin panel.
Select a user to edit. On the edit form, you will find a 'User name' field that you must fill to setup the user name.

User name length can be configured between 1 and 190 characters (see Module configuration).

User names are not case sensitive.

Just like Omeka classic, user names cannot contain whitespaces nor the following characters: + ! @ # $ % ^ & * . - _

### View user names

To view a user's name, browse to the Users admin panel and either click to view the user info, or click '...' to display the user's details pane.

### Log in using a user name

Login forms are modified to accept other identification methods than the original email input. Simply type your user name in the 'User name or email' input. Email can still be used as before, enabling the transition from email to usernames, and the occasional loss of ID.

## Module configuration

### User name minimum and maximum length

User names length can be configured in the module configuration panel.
Navigate to your Omeka s installation Modules panel, and clock Configure next to the UserNames module.

Default configuration is between 1 and 30 characters, same as Omeka classic.

Minimum length cannot be set below 1, and maximum length cannot be set above 190 characters.


## Known issues

See the Issues page.

## Contributing

Contributions are welcome. Please use Issues and Pull Requests workflows to contribute.

## Links

Some code and logic based on other Omeka S modules:
* GuestUser: https://github.com/biblibre/omeka-s-module-GuestUser
* Group: https://github.com/Daniel-KM/Omeka-S-module-Group
* MetaDataBrowse: https://github.com/omeka-s-modules/MetadataBrowse
* Omeka-S main repository: https://github.com/omeka/omeka-s

Check out my other modules:

* RestrictedSites: https://github.com/ManOnDaMoon/omeka-s-module-RestrictedSites
* UserNames: https://github.com/ManOnDaMoon/omeka-s-module-UserNames
* RoleBasedNavigation: https://github.com/ManOnDaMoon/omeka-s-module-RoleBasedNavigation
* Sitemaps: https://github.com/ManOnDaMoon/omeka-s-module-Sitemaps
* Siteswitcher: https://github.com/ManOnDaMoon/omeka-s-module-SiteSwitcher

## Licensing

The code in this project is licensed under GPLv3.
