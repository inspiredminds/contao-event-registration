<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Event Registration extension.
 *
 * (c) inspiredminds
 *
 * @license LGPL-3.0-or-later
 */

namespace InspiredMinds\ContaoEventRegistration\Export;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Date;
use Contao\Model\Collection;
use Doctrine\DBAL\Connection;
use InspiredMinds\ContaoEventRegistration\Config\ExportConfig;
use InspiredMinds\ContaoEventRegistration\Exception\ExportException;
use InspiredMinds\ContaoEventRegistration\Model\EventRegistrationModel;

class EventRegistrationExport
{
    private $framework;
    private $db;

    public function __construct(ContaoFramework $framework, Connection $db)
    {
        $this->framework = $framework;
        $this->db = $db;
    }

    public function getCsv(ExportConfig $config): \League\Csv\Writer
    {
        $this->framework->initialize();

        $records = EventRegistrationModel::findByPid($config->pid, ['order' => 'id ASC']);

        if (null === $records) {
            throw new ExportException('There are no records to export.');
        }

        $csv = \League\Csv\Writer::createFromFileObject(new \SplTempFileObject(32 * 1024 * 1024));
        $csv->setDelimiter($config->delimiter ?: ',');

        if ($config->excelCompatible) {
            $csv->setOutputBOM(\League\Csv\Writer::BOM_UTF8);
        }

        $table = EventRegistrationModel::getTable();

        Controller::loadDataContainer($table);

        $fieldConfig = $GLOBALS['TL_DCA'][$table]['fields'];

        $header = $this->getHeader($records, $fieldConfig);

        $csv->insertOne($header);

        foreach ($records as $record) {
            $row = [];

            foreach ($header as $headerField) {
                $value = $record->{$headerField};
                $config = $fieldConfig[$headerField];

                // Retrieve the label for the related record
                if (isset($config['foreignKey'])) {
                    [$foreignTable, $foreignLabelField] = explode('.', $config['foreignKey'], 2);

                    $foreignLabel = $this->db->executeQuery('SELECT '.$foreignLabelField.' FROM '.$foreignTable.' WHERE id = ?', [$value])->fetchOne();

                    if ($foreignLabel) {
                        $value = $foreignLabel;
                    }

                    if (!$value) {
                        $value = '';
                    }
                }

                // Transform dates
                if ($value && isset($config['eval']['rgxp']) && \in_array($config['eval']['rgxp'], ['date', 'time', 'datim'], true)) {
                    $value = (new Date($value))->{$config['eval']['rgxp']};
                }

                $row[] = $value;
            }

            $csv->insertOne($row);
        }

        return $csv;
    }

    private function getHeader(Collection $records, array $fieldConfig): array
    {
        // Add all DCA fields for the header
        $header = array_keys($fieldConfig);

        // Remove tstamp, form and form_data from the header
        $header = array_diff($header, ['tstamp', 'form', 'form_data']);

        $formDataHeaders = [];

        // Go through all form_data entries and add them as the header
        foreach ($records as $record) {
            foreach (json_decode($record->form_data ?? '', true) ?: [] as $key => $value) {
                $formDataHeaders[$key] = $key;
            }
        }

        $formDataHeaders = array_diff(array_keys($formDataHeaders), $header);

        return array_unique(array_merge($header, $formDataHeaders));
    }
}
