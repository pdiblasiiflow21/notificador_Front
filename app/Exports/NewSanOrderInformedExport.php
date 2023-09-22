<?php

declare(strict_types=1);

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Modules\NewSan\Entities\NewSanNotificationLog;
use Modules\NewSan\Entities\NewSanOrderInformed;

class NewSanOrderInformedExport implements FromCollection, WithHeadings, WithCustomCsvSettings, WithColumnWidths
{
    use Exportable;

    private $notificationLogId;

    private $columns;

    public function __construct($notificationLogId, array $columns = [])
    {
        $this->notificationLogId = $notificationLogId;
        $this->columns           = $columns;
    }

    public function collection(): Collection
    {
        $notificationLog = NewSanNotificationLog::findOrFail($this->notificationLogId);
        $notifiedIds     = json_decode($notificationLog->notified, true);
        $orders          = NewSanOrderInformed::whereIn('api_id', $notifiedIds)->get();

        return new Collection(
            $orders->map(function (NewSanOrderInformed $order) {
                return collect($order->toArray())
                    ->only(array_values($this->columns))                // Selecciono solo las columnas definidas
                    ->mapWithKeys(function ($item, $key) {              // Renombro las keys del array
                        $column = array_search($key, $this->columns, true);

                        return [$column => $item];
                    });
            })
        );
    }

    public function headings(): array
    {
        return array_keys($this->columns);
    }

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ';',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 50,
            'B' => 50,
            'C' => 50,
            'D' => 50,
            'E' => 150,
            'F' => 200,
            'G' => 100,
            'H' => 100,
        ];
    }
}
