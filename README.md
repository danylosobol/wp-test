# Soboldev WordPress Theme

This repository contains Soboldev WordPress theme starter kit.

## Requirements

Make sure all dependencies have been installed before moving on:

- [Docker Desktop](https://docs.docker.com/desktop/#download-and-install)
- lint, stylelint, docker, wsl extensions for VSCode installed.

Also it is nice to have some Linux image installed on your WSL2, like Ubuntu. Windows setup still possible but it will be slow due to slow access to the file system.

## Setup
During first run scripts would create new database with fresh wp setup.

Then start preparing the environment:
1. `cp .env.sample .env`
2. Set `ACF_PRO_KEY` in `.env` (ACF Pro license key), check other variables.
3. `composer acf-auth` (saves ACF Pro credentials to a local `auth.json`)
4. `composer install`
5. `npm i` (Node `^22.22.2 || >=24.15.0`)
6. Run `docker compose up`

## Development process
1. Run `docker compose up -d`
2. Run `npm run dev`
3. Access WP instance on https://localhost:4431/ for SSL (or other port set on .env as `DOCKER_SITE_PORT`)
4. Access PHPMyAdmin instance on https://localhost:4431/pma/
5. SMTP mail client available on https://localhost:4431/smtp/

## License
License: commercial.
It is strictly forbidden unauthorized copy of this theme.