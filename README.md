# SAML Auth Plugin (using SimpleSAMLphp) extending authplain for DokuWiki (plainsaml) #

## Description ##

This plugin provides an external authentication via SimpleSAMLphp Service Provider instance or local login with authplain plugin.


## Author ##

Dominik Volkamer <dominik.volkamer@fau.de> ([RRZE](https://www.rrze.fau.de/))

with the help of:

Oleg Britvin <oleg.britvin@fau.de> ([RRZE](https://www.rrze.fau.de/))


## Origins ##

This plugin is a fork of [authsaml plugin](https://www.dokuwiki.org/plugin:authsaml) by Sixto Martin <sixto.martin.garcia@gmail.com>, which is also based on the work of several other authors:

- Andreas Aakre Solberg, UNINETT, http://www.uninett.no
- François Kooman
- Thijs Kinkhorst, Universiteit van Tilburg / SURFnet bv
- Jorge Hervás <jordihv@gmail.com>
- Lukas Slansky <lukas.slansky@upce.cz>


## Requirements ##

This plugin is tested again:

- [DokuWiki](https://www.dokuwiki.org) Greebo (2018-04-22b)
- [SimpleSAMLphp](https://simplesamlphp.org) v1.17.2

(using it with older version of DokuWiki or SimpleSAMLphp may does not work)


## License ##

[GPL2](http://www.gnu.org/licenses/gpl.html)


## Installation ##

### SimpleSAMLphp ###

Please use the detailed and well described [installation and configuration instructions on SimpleSAMLphp homepage](https://simplesamlphp.org/docs/stable/simplesamlphp-install).


### plainsaml ###

1. Download this plugin for example with the help of the extension manager of DokuWiki ([URL](https://github.com/dreieinhalb/dokuwiki-plugin-plainsaml/zipball/master)).
2. Set configuration options for plainsaml with config manager of DokuWiki (options are descriped there).
3. Change DokuWiki's config option `authtype` to `plainsaml`.
