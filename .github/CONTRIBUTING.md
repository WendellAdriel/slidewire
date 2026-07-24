# CONTRIBUTING

Contributions are welcome, and are accepted via pull requests.
Please review these guidelines before submitting any pull requests.

For major changes, please open an issue first describing what you want to add or change.

## Process

1. Fork the project.
2. Create a new branch.
3. Code, test, commit, and push.
4. Open a pull request detailing your changes.

## Guidelines

* Please ensure the coding style passes by running `composer lint`.
* Send a coherent commit history, making sure each individual commit in your pull request is meaningful.
* You may need to [rebase](https://git-scm.com/book/en/v2/Git-Branching-Rebasing) to avoid merge conflicts.
* Please remember that we follow [SemVer](https://semver.org/).

## Setup

Clone your fork, then install the development dependencies:

```bash
composer install
npm install
```

## Browser prerequisites

Install the Playwright Chromium browser before running the browser suite:

```bash
npx playwright install chromium
```

## Lint

Run the linting and automated refactoring checks:

```bash
composer lint
```

## Tests

Run the full non-browser test suite:

```bash
composer test
```

Run the browser suite:

```bash
composer test:browser
```
