# IA Judge

Moodle activity module for code submission and AI-assisted evaluation.

## What it does

- Lets students submit code for evaluation.
- Lets teachers define a problem statement, rubric, allowed languages, and attempt limits.
- Sends submissions to a configured AI provider asynchronously through Moodle's task API.
- Stores the returned score and feedback in the Moodle database and gradebook.

## Requirements

- Moodle 5.2 or newer.
- PHP 8.3 or newer.
- A configured AI provider in site administration settings.

## Installation

1. Place this plugin in `mod/iajudge` inside the Moodle root.
2. Visit the Site administration notifications page, or upload the ZIP through the web installer.
3. Complete the database upgrade when prompted.

## Docker deploy

Build the deploy image:

```bash
docker build -t iajudge-deploy .
```

Run it against a Moodle root mounted at `/var/www/html`:

```bash
docker run --rm -v /path/to/moodle:/var/www/html iajudge-deploy
```

The container copies the plugin into `mod/iajudge` and leaves the Moodle upgrade step for the admin notifications page.

## Notes

- The background grading task is queued as an ad-hoc task when a student submits code.
- The plugin includes vendor libraries needed by the code editor and AI helpers.
