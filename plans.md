# Plans

## Extract restart-registry to a standalone plugin repo

**Goal:** Break the `src/plugins/restart-registry` plugin out into its own git repository so it can be independently tested and deployed.

### Current state

- Plugin lives at `src/plugins/restart-registry` inside the `local_wordpress` monorepo
- JS tests via Jest (`package.json` in the plugin dir)
- PHP tests via PHPUnit (`phpunit.xml`, `tests/` dir) — dev dependencies currently pulled from the parent `composer.json`
- Docker Compose WordPress environment lives in the parent repo; the plugin directory is volume-mounted as read/write
- No CI or deployment pipeline for the plugin today

### Proposed approach

1. **Extract to new repo** using `git filter-repo --subdirectory-filter src/plugins/restart-registry` on a clone of `local_wordpress` — this preserves the full git history for the plugin files.
2. **Add `composer.json`** to the plugin repo with the same dev dependencies currently in the parent (`phpunit/phpunit`, `brain/monkey`, `mockery/mockery`, `monolog/monolog`).
3. **Add a self-contained `docker-compose.yml`** to the plugin repo that spins up WordPress + MySQL + the plugin mounted, so tests and manual QA can run without the parent project.
4. **Add GitHub Actions CI** — two jobs: JS (Jest) and PHP (PHPUnit inside the Docker stack or via the WP test scaffold).
5. **Deployment artifact** — a GitHub Actions workflow that zips the plugin (excluding dev files) and either attaches it to a release or pushes via SFTP/WP-CLI to the live site.
6. **Wire local_wordpress back** — replace the plugin directory in `local_wordpress` with a git submodule pointing at the new repo so both stay in sync during local development.

### Decisions

- History: yes, preserve via `git filter-repo`
- New repo: `/home/andrew/projects/restart-registry`
- Submodule: yes, wire `local_wordpress` back as a submodule
- Deployment: manual zip upload (GitHub Actions release artifact)

### Todo

- [x] Clone `local_wordpress`, run `git filter-repo --subdirectory-filter src/plugins/restart-registry`, move result to `/home/andrew/projects/restart-registry`
- [x] Add `composer.json` to the plugin repo (phpunit ^12, brain/monkey ^2.7, mockery ^1.6, monolog ^2.0)
- [x] Fix `tests/bootstrap.php`: change `dirname(__DIR__, 4)` → `dirname(__DIR__)` (vendor now at plugin root)
- [x] Update `.gitignore` (add `vendor/`, `node_modules/`)
- [x] Add `docker-compose.yml` for standalone WP dev environment
- [x] Add `.github/workflows/ci.yml` (Jest + PHPUnit jobs)
- [x] Add `.github/workflows/release.yml` (zip plugin, attach to GitHub release)
- [x] Remove plugin dir from `local_wordpress`, re-add as git submodule pointing at the new repo

### Remaining manual steps

1. Create the `restart-registry` repo on GitHub and push: `git remote add origin git@github.com:amneher/restart-registry.git && git push -u origin --all`
2. Update `.gitmodules` in `local_wordpress` to point at the GitHub URL instead of the local path, then commit and push.
