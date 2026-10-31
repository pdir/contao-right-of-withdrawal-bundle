<?php

namespace Pdir\ContaoRightOfWithdrawalBundle\Backend;

class CsvExporter
{
    private const COLUMNS = ['rootPage', 'sequenceNumber', 'type', 'jurisdiction', 'language', 'name', 'contract', 'contractPart', 'email', 'cancellationType', 'cancellationReason', 'terminationDate', 'receivedAt', 'mailStatus', 'orderId', 'orderMatch', 'status', 'notes', 'hash', 'previousHash'];

    /**
     * @param iterable<array<string, mixed>> $rows
     */
    public function export(iterable $rows): string
    {
        $stream = fopen('php://temp', 'r+');

        fputcsv($stream, self::COLUMNS, ';', '"', '');

        foreach ($rows as $row) {
            $line = [];

            foreach (self::COLUMNS as $column) {
                $value = (string) ($row[$column] ?? '');

                $line[] = 'receivedAt' === $column ? date('c', (int) $value) : $this->neutralize($value);
            }

            fputcsv($stream, $line, ';', '"', '');
        }

        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);

        return "\xEF\xBB\xBF" . $csv;
    }

    /**
     * The values come from a public form and spreadsheets execute formulas.
     */
    private function neutralize(string $value): string
    {
        return '' !== $value && \in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true) ? "'" . $value : $value;
    }
}
