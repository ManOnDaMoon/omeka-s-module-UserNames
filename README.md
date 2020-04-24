# omeka-s-module-UserNames
Module for Omeka S

This module adds user name capability to Omeka S.
Users or administrators can setup user names on a per user basis. Users can then use this as a login credential just like they usually do with their email.

## Installing / Getting started

UserNames is compatible with Omeka-S v2.

* Download and unzip in your `omeka-s/modules` directory.
* Rename the uncompressed folder to `UserNames`.
* Log into your Omeka-S admin backend and navigate to the `Modules` menu.
* Click `Install` next to the UserNames module.

## Features

This module includes the following features:

* Associate a user name your users' accounts on an Omeka-S install. These user names are unique to each user.
* Log in using either the user email, as usual, or using the configured user name.
* User names are distinct from display names.

### Set-up a user name

To configure a new user name, got to your admin dashboard and to the User admin panel.
Select a user to edit. On the edit form, you will find a 'User name' field that you must fill to setup the user name.

### Log in using a user name

Login forms are modified to accept other identification methods than the original email input. Simply type your user name in the 'User name or email' input.

## Module configuration

### User name minimum and maximum length

User names length can be configured in the module configuration panel.
Navigate to your Omeka s installation Modules panel, and clock Configure next to the UserNames module.

Default configuration is between 1 and 30 characters.

Minimum length cannot be set below 1, and maximum length cannot be set above 190 characters.


## Known issues

See the Issues page.

## Contributing

Contributions are welcome. The module is in early development stage and could do with more advanced usage and testing.

## Links

Some code and logic based on other Omeka-S modules:
- GuestUser: https://github.com/biblibre/omeka-s-module-GuestUser
- Group: https://github.com/Daniel-KM/Omeka-S-module-Group
- MetaDataBrowse: https://github.com/omeka-s-modules/MetadataBrowse
- Omeka-S main repository: https://github.com/omeka/omeka-s


## Licensing

The code in this project is licensed under GPLv3.