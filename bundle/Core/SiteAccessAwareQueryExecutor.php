<?php

declare(strict_types=1);

namespace Netgen\Bundle\Ibexa2FABundle\Core;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ForwardCompatibility\DriverResultStatement;
use Doctrine\DBAL\ForwardCompatibility\DriverStatement;
use Doctrine\DBAL\ForwardCompatibility\Result;
use Doctrine\Persistence\ManagerRegistry as Registry;
use Ibexa\Bundle\Core\ApiLoader\RepositoryConfigurationProvider;

use function trim;

final class SiteAccessAwareQueryExecutor
{
    private Registry $registry;

    private RepositoryConfigurationProvider $repositoryConfigurationProvider;

    public function __construct(Registry $registry, RepositoryConfigurationProvider $repositoryConfigurationProvider)
    {
        $this->registry = $registry;
        $this->repositoryConfigurationProvider = $repositoryConfigurationProvider;
    }

    public function __invoke(string $query, array $params, array $types): DriverResultStatement|Result|DriverStatement|int|string
    {
        $cleanQuery = trim($query);

        /** @var Connection $connection */
        $connection = $this->registry->getConnection($this->getConnectionName());

        if (0 === mb_stripos($cleanQuery, 'select')) {
            return $connection->executeQuery($cleanQuery, $params, $types);
        }

        return $connection->executeStatement($cleanQuery, $params, $types);
    }

    private function getConnectionName(): string
    {
        $config = $this->repositoryConfigurationProvider->getRepositoryConfig();

        return $config['storage']['connection'] ?? 'default';
    }
}
