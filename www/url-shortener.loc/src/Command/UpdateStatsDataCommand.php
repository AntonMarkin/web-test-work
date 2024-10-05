<?php

namespace App\Command;

use App\Entity\ServiceField;
use App\Service\UrlManager;
use DateTimeImmutable;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class UpdateStatsDataCommand extends Command
{
    protected static $defaultName = 'app:update-stats-data';
    protected static $defaultDescription = 'Add a short description for your command';

    private $statsEndpoint;
    private $urlManager;
    private $entityManager;
    private $client;

    public function __construct(
        $statsEndpoint,
        UrlManager $urlManager,
        ManagerRegistry $doctrine,
        HttpClientInterface $client
    ) {
        $this->statsEndpoint = $statsEndpoint;
        $this->urlManager = $urlManager;
        $this->entityManager = $doctrine->getManager();
        $this->client = $client;
        parent::__construct();
    }
    protected function configure(): void
    {
        $this
            ->setDescription('Sends URLs with the creation date to the specified endpoint to compile statistics.')
            ->setHelp('This command sends URLs with the creation date to the specified endpoint to compile statistics.')
        ;
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $fieldRepository = $this->entityManager->getRepository(ServiceField::class);
        if(empty($serviceField = $fieldRepository->findOneBy(['name' => ServiceField::URL_STATS_UPD_DATE]))) {
            $serviceField = new ServiceField();
            $serviceField->setName(ServiceField::URL_STATS_UPD_DATE);
        }

        $date = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $serviceField->getValue()) ?: null;
        $urlData = $this->urlManager->getUrlsInfo($date);
        if (empty($urlData)) {
            $io->info('There are no new urls to send.');
            return Command::SUCCESS;
        }
        foreach ($urlData as &$url) {
            $url['createdDate'] = $url['createdDate']->format('Y-m-d H:i:s');
        }
        $serviceField->setValue($urlData[0]['createdDate']);

        try {
            $response = $this->client->request('POST', $this->statsEndpoint, ['json' => ['urls' => $urlData]]);
            if ($response->getStatusCode() !== 200) {
                throw new \Exception('HTTP error ' . $response->getStatusCode());
            }
        } catch (\Exception $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        $io->success('Statistics have been sent successfully');

        $this->entityManager->persist($serviceField);
        $this->entityManager->flush();
        return Command::SUCCESS;
    }
}
