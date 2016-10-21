<?php

namespace App\AvaHelper;

use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ExcelReader
{

    /**
     * Name of file
     * @var
     */
    public $fileName;

    /**
     * @var
     */
    public $safeFileName;

    /**
     * Path to file
     * @var
     */
    public $filePath;

    /**
     * Columns to look for in excel
     * @var
     */
    public $excelColumns;

    /**
     * Subscriber validation rules
     * @var
     */
    public $subscriberRules;

    /**
     * ExcelReader constructor.
     * @param $fileName
     * @param $safeFileName
     * @param $filePath
     * @param $excelColumns
     * @param $rules
     * @internal param $filename
     */
    public function __construct($fileName, $safeFileName, $filePath, $excelColumns, $rules)
    {
        $this->fileName = $fileName;
        $this->safeFileName = $fileName;
        $this->filePath = $filePath;
        $this->excelColumns = (array) $excelColumns;
        $this->subscriberRules = $rules;
    }

    /**
     * Compute good records from the file import and return a set of good/bad results or null if file doesn't exist
     * @return mixed
     */
    public function getResults()
    {
        $safeFileName = $this->safeFileName;

        $results = cache()->remember("resultsOfImport$safeFileName", 30, function() {
            $file = $this->filePath;

            if ( file_exists($file) ) {
                $excel = Excel::selectSheetsByIndex(0)->load($file, function ($reader) {
                    $excelColumns = $this->excelColumns;
                    $rules = $this->subscriberRules;
                    $sheet = $reader->get($excelColumns);
                    $goodOnes = [];
                    $badRows = [];
                    $rowsNum = 0;

                    if ($sheet) {
                        foreach ($sheet as $key => $row) {
                            $subscriber = [
                                'first_name' => ucwords(strtolower( Str::ascii($row->{$excelColumns['first_name']}) )),
                                'last_name' => ucwords(strtolower( Str::ascii($row->{$excelColumns['last_name']}) )),
                                'email' => strtolower($row->{$excelColumns['email']})
                            ];

                            $validator = validator()->make($subscriber, $rules);

                            if ($validator->fails())
                                $badRows[] = $key + 2;
                            else
                                $goodOnes[] = (object) $subscriber;

                            $rowsNum++;
                        }
                    }

                    $reader->goodOnes = $goodOnes;
                    $reader->badRows = $badRows;
                    $reader->rowsNum = $rowsNum;
                }, 'UTF-8');

                return (object)['goodOnes' => $excel->goodOnes, 'badRows' => $excel->badRows, 'rowsNum' => $excel->rowsNum];
            }
            else
                return null;
        });

        if ( ! $results )
            cache()->forget("resultsOfImport$safeFileName");

        return $results;
    }


}