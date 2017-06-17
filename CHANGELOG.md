# Change Log
All notable changes to this project will be documented in this file. This project adheres to
[Semantic Versioning](http://semver.org/) and [this changelog format](http://keepachangelog.com/).

## Unreleased

### Added
- Standard iterator can now be cast to a collection using the `collect` method.

### Changed
- Abstract standard iterator is now immutable by default.
- Standard iterator method `getAll` is now `all`.
- Methods on the standard iterator interface are now type hinted to receive a `Closure` rather than a `callable`.

## [0.1.1] - 2016-09-30

### Fixed
- `AbstractStandardIterator` did not implement `StandardIteratorInterface`

## [0.1.0] - 2016-04-23

Initial development release.

### Added
- `Collection` class
- `StandardIteratorInterface`, plus a trait and abstract class for easily implementing the interface.
