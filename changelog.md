# Changelog

All notable changes to this project will be documented in this file.

## [1.0.0-beta.2] - 2025-05-21
### Changed
- Date output in the module template is now locale-aware using `IntlDateFormatter`.
  This ensures month names and date formats match the user's Joomla language setting.

## [1.0.0-beta.1] - 2025-05-03
### Added
- Displays latest observations from a specific iNaturalist user
- Optional filter by organism group or custom taxon ID
- Configurable number of observations
- Server-side caching (configurable duration)
- Fully GDPR compliant
- Multilingual (English and German)
- Automatic language selection based on the visitor’s site language (species names, month names)
- GPL v3 licensed
