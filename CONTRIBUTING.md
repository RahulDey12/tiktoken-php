# Contributing to tiktoken-php

Thank you for considering contributing to tiktoken-php!

## How to Contribute

### Suggesting Enhancements

Create an issue on GitHub with:
- A clear title
- Detailed description
- Relevant use cases

### Submitting Pull Requests

1. **Fork the repository** to your GitHub account.
2. **Clone your fork**: `git clone https://github.com/YOUR-USERNAME/tiktoken-php.git`
3. **Create a branch**: `git checkout -b feature/your-feature-name`
4. **Make your changes**, following the project’s coding standards.
5. **Refactor your code**: `composer refactor`
6. **Push to your fork**: `git push origin feature/your-feature-name`
7. **Open a pull request** with a clear description and reference any related issues.

## Guidelines

- Ensure your code follows the project’s coding style by running `composer lint`.
- Send a coherent commit history, making sure each individual commit in your pull request is meaningful.
- You may need to [rebase](https://git-scm.com/book/en/v2/Git-Branching-Rebasing) to avoid merge conflicts.
- Remember that we follow [Semantic Versioning (SemVer)](http://semver.org/).

## Tests

Run all tests:
```bash
composer test
```

Check code quality:
```bash
composer test:refactor
```

Check types:
```bash
composer test:types
```

Unit tests:
```bash
composer test:unit
```

Thank you for contributing to tiktoken-php!
