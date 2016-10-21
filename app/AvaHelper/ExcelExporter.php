<?php

namespace App\AvaHelper;

use Maatwebsite\Excel\Facades\Excel;

class ExcelExporter
{
    /**
     * Subscribers collection
     * @var
     */
    public $subscribers;

    /**
     * Export filename
     * @var
     */
    public $exportFileName;

    /**
     * ExcelExporter constructor.
     * @param $subscribers
     * @param $fileName
     */
    public function __construct($subscribers, $fileName)
    {
        $this->subscribers = $subscribers;
        $this->exportFileName = $fileName;
    }


    /**
     * Generate an excel file of supplied subscribers
     * @return mixed
     */
    public function generateSubscribersExport()
    {
        return Excel::create($this->exportFileName, function($excel) {
            $subscribers = $this->subscribers;

            $activeArr = [];
            $inactiveArr = [];

            if ( count($subscribers) ) {
                foreach ($subscribers as $subscriber) {
                    $mailing_lists = '✗';

                    if ( $num = count($subscriber->mailing_lists) ) {
                        $mailing_lists = '';
                        foreach ($subscriber->mailing_lists as $key => $mailing_list) {
                            $mailing_lists .= $mailing_list->name;
                            $mailing_lists .= ($key == $num - 1) ? '' : '; ';
                        }
                    }

                    $subArr = [
                        'First Name' => $subscriber->first_name,
                        'Last Name' => $subscriber->last_name,
                        'Email' => $subscriber->email,
                        'Active' => $subscriber->active ? '✔' : '✗',
                        'Trash' => $subscriber->is_deleted ? '✔' : '✗',
                        'Mailing Lists' => $mailing_lists
                    ];

                    if ($subscriber->active)
                        $activeArr[] = $subArr;
                    else
                        $inactiveArr[] = $subArr;
                }
            }

            $excel->sheet('Active Subscribers', function($sheet) use ($activeArr) {
                $sheet->fromArray($activeArr);
            });

            $excel->sheet('Inactive Subscribers', function($sheet) use ($inactiveArr) {
                $sheet->fromArray($inactiveArr);
            });

        })->download('xls');
    }
}