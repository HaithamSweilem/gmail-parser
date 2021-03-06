# Gmail Parser

A simple PHP scripting application which fetch emails
from your Gmail account according to a filter and parses them
for information.

The application demonstrates this by parsing emails received for
orders from amazon.com and amazon.co.uk.

## Prerequisites

1. ๐ฐ [RabbitMQ](https://www.rabbitmq.com/) message broker installed and running.
2. ๐งจ PHP 7.4+
3. ๐ผ [composer](https://getcomposer.org/)
4. A Google Cloud Platform project with the **Gmail API** enabled. 
To create a project and enable an API, refer to
[Create a project and enable the API](https://developers.google.com/workspace/guides/create-project)

## Install Composer Dependencies
> composer install

This installs all dependencies required by the project.

## Create a Google Cloud Platform Project with Gmail API Enabled โจ
Refer to [the docs](https://developers.google.com/workspace/guides/create-project)
and create a Google Cloud Platform project.

Enable the Gmail API.

Download the `credentials.json` file and put it inside `/app/lib/`.
This file will be used in the OAuth flow to communicate with the 
Google Cloud Project.

## Initiate OAuth Flow ๐งฆ
> composer auth

This runs a local web server in order to initiate the OAuth flow.

Navigate to `localhost:8080` and authenticate with your Gmail account.

This will save auth information (access/refresh tokens) in a file called `token.json`
 under `/app/lib`.

You can change the port `8080` in composer.json file.

## Run Email Fetcher ๐
> composer fetch

Runs a PHP script for fetching emails and pushing a relevant message
to RabbitMQ broker.

After fetching, the script will exit.

Consider running this script as a CRON job.

## Run Email Parser ๐ช
> composer parse

Runs a script which consumes messages from RabbitMQ broker and parses relevant
information from messages like (item, price, date of purchase, store).

The script will listen to messages indefinitely (`--timeout 0`), unless it throws an error,
or is stopped by a control signal (e.g. CTRL+C)

## Enjoy! ๐
