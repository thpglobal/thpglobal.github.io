# Google App Engine

The Hunger Project (thp.org) utilizes GSuite applications to facilitate global collaboration, and so established its custom applications on the Google Cloud Platform - specifically CloudSQL and the Google App Engine (GAE) PHP 5.5 Standard Environment.

Recently, GAE PHP 7.2 Standard Environment because available, promising greater speed, greater compatibility with 3rd party packages and a longer future, but removing certain features and requiring certain design changes.

* Built-in Google Sign-in is now gone, and must be replaced with calls to various packages
* PHP 7.2 uses a single user-facing script for redirection rather than app.yaml.
* The good news - ZipArchive did not work in GAE PHP 5.5 but does under PHP 7.2
