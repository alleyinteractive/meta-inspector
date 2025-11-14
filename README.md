# Meta Inspector

[![All Pull Request Tests](https://github.com/alleyinteractive/meta-inspector/actions/workflows/all-pr-tests.yml/badge.svg)](https://github.com/alleyinteractive/meta-inspector/actions/workflows/all-pr-tests.yml)

Display the meta data for WordPress objects in the admin to assist in debugging.

## Releases

This package is released via Packagist for installation via Composer. It follows semantic versioning conventions.

### Install

Requires Composer and PHP >= `8.2`.

### Use

You can install the package via composer:

```sh
composer require alleyinteractive/meta-inspector
```

## Usage

![Screenshot of the settings panel](https://user-images.githubusercontent.com/346399/194622945-e3f8f24c-9399-43f4-9352-c1c1e025089f.png)

Once activated, the plugin will add meta boxes to the following object types
that expose the meta data for the object:

- Comments
- Terms
- Posts
- Users
- Groups (BuddyPress)
- Activities (BuddyPress)

## Testing

Run `composer test` to run tests against the Coding Standards.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

This project is actively maintained by [Alley](https://github.com/alleyinteractive).
Like what you see? [Come work with us](https://alley.co/careers/).

![Alley logo](https://avatars.githubusercontent.com/u/1733454?s=200&v=4)

## License

This project is licensed under the [GNU Public License (GPL) version 2](LICENSE) or later.
