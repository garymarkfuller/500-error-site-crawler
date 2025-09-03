## 500 Error Site Crawler

### Installation

Clone the repository and run `composer install`.

### Dependencies

You will need to be able to use a headless version of Google Chrome installed via the terminal. 

### Compatability

This has been tested in WSL running Apache2, and used for testing dev copies of sites with .test urls defined in the Windows hosts file. I've no reason to assume this wouldn't work on Linux.

### Usage

Run the command below including https:// in the URL.

`bin/console CrawlSite $SITE_URL`

### What it does

This project uses Roach PHP and Symfony which is probably overkill, but is quick to set up. The command uses a spider to crawl the site based upon page links and should output any pages on it that have 500 errors into the command line upon completion. It should ignore external links and duplicate links.

### Changelog

Added Readme, Added ErrorSpider and CrawlSite classes 
