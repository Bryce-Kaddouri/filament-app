<?php

namespace App\Models;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrendProviderPriceByMonthChartData extends Model
{
    use HasFactory;

    protected $fillable = ['month'];

    /**
     * @param Collection<int, TrendProviderPriceByMonth> $prices
     */
    public static function getChartData(Collection $stats, array $distinctMonths, array $providers): Collection
    {
        $chartData = collect([]);

        foreach($distinctMonths as $month){
            $dataPoint = [
                'month' => $month,
            ];
            $statsForMonth = $stats->where('month', $month);
            foreach($statsForMonth as $stat){
                $dataPoint[$stat->provider_name] = $stat->average_price;
            }
            foreach($providers as $provider){
                if(!isset($dataPoint[$provider['name']])){
                    // Check if the provider was in the price for the previous month
                    $previousData = $chartData->where('month', '<', $month)->last();
                    if(isset($previousData[$provider['name']])){
                        $dataPoint[$provider['name']] = $previousData[$provider['name']];
                    } else {
                        $dataPoint[$provider['name']] = null;
                    }
                }
            }
            $chartData->push($dataPoint);
        }

        return $chartData;
    }
}
