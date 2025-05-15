<?php

namespace App\Services\ExternalLogger\Logger;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use LaravelDoctrine\ORM\Loggers\Logger;
use Psr\Log\LoggerInterface as Log;

class FileLogger implements Logger
{
    /**
     * @var Log
     */
    protected $logger;

    /**
     * @param Log $logger
     */
    public function __construct(Log $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param EntityManagerInterface $em
     * @param Configuration          $configuration
     */
    public function register(EntityManagerInterface $em, Configuration $configuration)
    {
        $logger = new ExternalSqlLogger(request());
        $configuration->setSQLLogger($logger);
    }
}
