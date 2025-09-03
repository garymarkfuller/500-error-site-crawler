<?php

namespace App\Command;

use App\Spiders\ErrorSpider;
use RoachPHP\Roach;
use RoachPHP\Spider\Configuration\Overrides;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'CrawlSite',
    description: 'Crawl website to look for 500 errors',
)]
class CrawlSiteCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Crawl a website for 500 errors')
            ->setHelp('This command allows you to crawl a specified website to identify any pages that return a 500 error status code. You will need to include https:// in the website url for it to work')
            ->addArgument('website', InputArgument::REQUIRED, 'The website URL to crawl');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $website = $input->getArgument('website');
        $io->note('Crawling ' . $website);
        $errorPages = Roach::collectSpider(ErrorSpider::class, new Overrides(startUrls:[$website]));
        foreach ($errorPages as $page) {
            if ($page['status_code'] === 500) {
                $output->writeln("500 Error: " . $page['url']);
            }
        }
        return Command::SUCCESS;
    }
}
