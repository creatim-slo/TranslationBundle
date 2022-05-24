<?php

declare(strict_types=1);

namespace Kilik\TranslationBundle\Services;

class ReporterService
{
    private array $reporters = [];

    public function addReporter(callable $reporter, string $reporterKey = null): string
    {
        if (null === $reporterKey) {
            $reporterKey = sprintf('reporter%d', count($this->reporters) + 1);
        }

        $this->reporters[$reporterKey] = $reporter;

        return $reporterKey;
    }

    public function removeReporter(string $reporterKey): void
    {
        unset($this->reporters[$reporterKey]);
    }

    public function report(string $data): void
    {
        foreach ($this->reporters as $reporter) {
            $reporter($data);
        }
    }
}
