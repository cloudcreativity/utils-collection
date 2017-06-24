# Change Log
All notable changes to this project will be documented in this file. This project adheres to
[Semantic Versioning](http://semver.org/) and [this changelog format](http://keepachangelog.com/).

## Unreleased

### Added
- Collection now has a static `create` method, for fluent construction.

### Changed
- Collection constructor now uses variable-length arguments.
- Collection casting now longer accepts objects unless they are traversable.
- Collection now implements `IteratorAggregate` rather than `Iterator`.
- Collection object modifiers (e.g. `addObjects`) are now always strict.
- Collection `replace` method now uses variable-length arguments.
- Collection `chunk` method now returns a collection of collection chunks.
- Collection methods with strict parameters have been split into two functions:
  - `add` and `addStrict`
  - `unique` and `uniqueStrict`
  - `remove` and `removeStrict`
  - `without` and `withoutStrict`
  - `search` and `searchStrict`
  - `contains` and `containsStrict`
  - `equals` and `equalsStrict`
  - `indexOf` and `indexOfStrict`

### Removed
- Remove the deprecated `Collection::toArray` method.

### Deprecated
- The following collection methods are deprecated in preference of methods with variable-length arguments...
  - `addMany`: use `add` or `addStrict`
  - `addObject`: use `addObjects`
  - `pushMany`: use `push`
  - `pushObject`: use `pushObjects`
  - `removeMany`: use `remove` or `removeStrict`
  - `unshiftMany`: use `unshift`
  - `unshiftObject`: use `unshiftObjects`

## [0.2.0] - 2017-06-17

### Added
- Standard iterator can now be cast to a collection using the `collect` method.
- `isNotEmpty` method added to collection and standard iterator.
- Collection `all` method to get the collection as an array.

### Changed
- Abstract standard iterator is now immutable by default.
- Standard iterator method `getAll` is now `all`.
- Methods on the standard iterator interface are now type hinted to receive a `Closure` rather than a `callable`.

### Fixed
- Invalid variable name in `Collection::invoke`.

### Deprecated
- `Collection::toArray` is deprecated in favour of `Collection::all`

## [0.1.1] - 2016-09-30

### Fixed
- `AbstractStandardIterator` did not implement `StandardIteratorInterface`

## [0.1.0] - 2016-04-23

Initial development release.

### Added
- `Collection` class
- `StandardIteratorInterface`, plus a trait and abstract class for easily implementing the interface.
