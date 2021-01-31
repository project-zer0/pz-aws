# Project Zer0 AWS Module

A pz module for AWS - Official Amazon AWS command-line interface

## Install

Via composer:

```shell
$ composer require --dev project-zer0/pz-aws
```

## Configuration

This module provides following config block to `.pz.yaml` file

```yaml
project-zer0:
  aws:
    image: amazon/aws-cli       # Docker image name to use for aws command
    config_dir: $PZ_PWD/.pz/.aws # A path where to keep AWS config
```


## Commands

This module provides these commands in `pz` tool

```shell
$ pz aws:cli                 [aws] The  AWS  Command  Line  Interface is a unified tool to manage your AWS services.
$ pz aws:login               Configure AWS and login into Docker AWS ECR Registry
```

## Testing

Run test cases

```bash
$ composer test
```

Run test cases with coverage (HTML format)

```bash
$ composer test-coverage
```

Run PHP style checker

```bash
$ composer cs-check
```

Run PHP style fixer

```bash
$ composer cs-fix
```

Run all continuous integration tests

```bash
$ composer ci-run
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.


## License

Please see [License File](LICENSE) for more information.
