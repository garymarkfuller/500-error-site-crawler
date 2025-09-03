<?php

namespace App\Spiders;

use RoachPHP\Downloader\Middleware\RequestDeduplicationMiddleware;
use RoachPHP\Downloader\Middleware\UserAgentMiddleware;
use RoachPHP\Http\Response;
use RoachPHP\Http\Request;
use RoachPHP\Spider\BasicSpider;
use RoachPHP\ItemPipeline\Item;
use Generator;
use Exception;

/**
 * A simple spider to crawl a website and report on 500 errors.
 */
class ErrorSpider extends BasicSpider
{
    /**
     * The URL where the spider will start its crawl.
     * In this case, we're targeting the provided test site.
     * @var array
     */
    public array $startUrls = [];

    /**
     * The item processors to use
     *
     * @var array
     */
    public array $itemProcessors = [];

    /**
     * The downloader middleware to use
     *
     * @var array
     */
    public array $downloaderMiddleware = [
        [
            UserAgentMiddleware::class,
            ['userAgent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36'],
        ],
        RequestDeduplicationMiddleware::class,
    ];

    /**
     * The main parsing method for the spider.
     * It's called for every successful HTTP response.
     *
     * @param Response $response The response object from the crawled URL.
     * @return Generator<Item|Request>
     */
    public function parse(Response $response): Generator
    {
        // Check if the current response's status code is a 500.
        // The getStatus() method is used to retrieve the HTTP status code.
        if ($response->getStatus() === 500) {
            // Log the 500 error. In a real-world scenario, you might
            // save this to a database or a file.
            yield $this->item([
                'url' => (string) $response->getUri(),
                'status_code' => $response->getStatus(),
            ]);
        }

        // Now, yield new requests to continue crawling the site.
        // We find all the <a> tags and create a new request for each.
        try {
            // Use the DomCrawler filter and links methods to get all anchor tags.
            $links = $response->filter('a')->links();
            foreach ($links as $link) {
                // Yield a new request for each discovered link.
                // This tells Roach to crawl the link as well.
                $url = (string) $link->getUri();
                if (str_starts_with($url, $response->getUri()) && !str_contains($url, '#') && !str_contains($url, '?')) {
                    yield $this->request('GET', $url, 'parse');
                }
            }
        } catch (Exception $e) {
            // This is a common way to handle cases where no links are found on a page,
            // as the filter method will throw an exception if the selector doesn't match anything.
            // We can just ignore this and move on.
        }
    }
}
