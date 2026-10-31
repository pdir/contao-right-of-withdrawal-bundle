<?php

namespace Pdir\ContaoRightOfWithdrawalBundle\Declaration;

/**
 * Detects declarations that were changed or removed after they were received.
 * A chain may start at any number, as the retention period removes old entries.
 */
class ChainVerifier
{
    public function __construct(private readonly DeclarationRepository $repository)
    {
    }

    /**
     * @return list<string> The problems found
     */
    public function verify(): array
    {
        $problems = [];
        $previous = [];

        foreach ($this->repository->findAll() as $row) {
            $root = (int) $row['rootPage'];
            $number = (int) $row['sequenceNumber'];

            if (Declaration::computeHash($row) !== $row['hash']) {
                $problems[] = \sprintf('Root %d, no. %d: the content does not match its checksum.', $root, $number);
            }

            if (isset($previous[$root])) {
                if ($number !== $previous[$root]['number'] + 1) {
                    $problems[] = \sprintf('Root %d: entries missing between no. %d and no. %d.', $root, $previous[$root]['number'], $number);
                } elseif ($row['previousHash'] !== $previous[$root]['hash']) {
                    $problems[] = \sprintf('Root %d, no. %d: the link to the previous entry is broken.', $root, $number);
                }
            }

            $previous[$root] = ['number' => $number, 'hash' => $row['hash']];
        }

        return $problems;
    }
}
