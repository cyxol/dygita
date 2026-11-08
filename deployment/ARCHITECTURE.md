# Dygita responsibilities

`functions.php` loads theme modules; it does not register routes or mutate the database on page reads.

- `integration/DygitaRuntime`: early Typecho initialization for actions and admin requests.
- `lib/Runtime.php`: explicit activation/deactivation and page routing.
- `lib/ThemeConfig.php`: editor-facing theme options and custom fields.
- `lib/Repository.php`: public visibility rules, bounded search and atomic numeric counters.
- `lib/Like.php`, `Search.php`, `Preference.php`: request validation and response contracts.
- `lib/Submission.php`: HTML allowlist and core draft creation.
- `lib/Catalog.php`: request-local rendering after Typecho permission checks.
- `lib/Assets.php`: content-based static asset versioning.
- `lib/Presentation.php`, `Content.php`: legacy-compatible template helpers.

Public search is queried live rather than persisted into a TEXT option: edits, deletion,
password changes and visibility changes therefore cannot leave stale public excerpts.
Catalogs use filtered content and never read persisted raw HTML. Numeric counters use
`int_value` as their authority, with `str_value` mirrored for legacy readers.

This remains a Typecho monolith. Some legacy template helpers still contain rendering
and queries; they can be moved behind Repository methods as those features evolve.
No Typecho core files are modified.

Integration tests use only the explicitly named disposable database. They must never
be pointed at the live blog. See `tools/test-bootstrap.php` for the guard.
