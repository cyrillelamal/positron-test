<?php

namespace App\Service;

use Doctrine\DBAL\Platforms\MySQLPlatform;

trait PlatformInformation
{
    private function isUsingMysql(): bool
    {
        return $this->getEntityManager()
                ->getConnection()
                ->getDriver()
                ->getDatabasePlatform() instanceof MySQLPlatform;
    }
}
