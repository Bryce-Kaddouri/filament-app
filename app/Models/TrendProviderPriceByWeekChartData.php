<?php

namespace App\Models;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrendProviderPriceByWeekChartData extends Model
{
    use HasFactory;

    protected $fillable = ['week'];

    /**
     * @param Collection<int, TrendProviderPriceByWeek> $stats
     */
    public static function getChartData(Collection $stats, array $distinctWeeks, array $providers): Collection
    {
        $chartData = collect([]);

        foreach($distinctWeeks as $week){
            $dataPoint = [
                'week' => $week,
            ];
            $statsForWeek = $stats->where('week', $week);
            foreach($statsForWeek as $stat){
                $dataPoint[$stat->provider_name] = $stat->average_price;
            }
            foreach($providers as $provider){
                if(!isset($dataPoint[$provider['name']])){
                    // Check if the provider was in the price for the previous week
                    $previousData = $chartData->where('week', '<', $week)->last();
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
