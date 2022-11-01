<?php

class AdminImportController extends AdminImportControllerCore
{
    public function addressImport($offset = false, $limit = false, $validateOnly = false)
    {
        if (Module::isEnabled('loqate') && Configuration::get('LOQ_ADDR_VERIFICATION_ADDRESS_IMPORT')) {
            $loqate = Module::getInstanceByName('loqate');
            $this->receiveTab();
            $handle = $this->openCsvFile($offset);
            if (!$handle) {
                return false;
            }

            AdminImportController::setLocale();
            $addresses = [];
            $line_count = 0;
            $nb = 0;
            $toSkip = (int)Tools::getValue('skip');

            while ($line = fgetcsv($handle, MAX_LINE_SIZE, $this->separator)) {
                ++$line_count;
                if ($this->convert) {
                    $line = $this->utf8EncodeArray($line);
                }
                if (count($line) == 1 && $line[0] == null) {
                    continue;
                }
                $addresses[] = AdminImportController::getMaskedRow($line);
                $addresses[$nb]['row'] = $line_count + $toSkip;
                ++$nb;
            }

            $allRowsResult = [];
            if (count($addresses)) {
                $batches = array_chunk($addresses, 100);
                foreach ($batches as $batch) {
                    $allRowsResult = array_merge($allRowsResult, $loqate->verifyAddress($batch));
                }
                foreach ($allRowsResult as $index => $validAddress) {
                    if (!$validAddress) {
                        $this->errors[] = $loqate->l('Invalid address at row #') . $addresses[$index]['row'];
                    }
                }
            }
            $this->closeCsvFile($handle);
        }
        return parent::addressImport($offset, $limit, $validateOnly);
    }

}
