<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create the trend_provider_price_by_month view
        DB::statement("
            CREATE OR REPLACE VIEW trend_provider_price_by_month AS 
            SELECT 
                p.provider_id,
                prov.name as provider_name,
                DATE_FORMAT(p.effective_date, '%Y-%m-%d') AS month,
                AVG(p.price) AS average_price
            FROM 
                prices p
            JOIN 
                providers prov ON p.provider_id = prov.id
            GROUP BY 
                p.provider_id, prov.name, month
            ORDER BY 
                p.provider_id, month;
        ");

        // Create the trend_provider_price_by_week view
        DB::statement("
            CREATE OR REPLACE VIEW trend_provider_price_by_week AS 
            SELECT 
                p.provider_id,
                prov.name AS provider_name,
                DATE_FORMAT(p.effective_date, '%Y-%u') AS week,
                DATE_ADD(p.effective_date, INTERVAL(1 - DAYOFWEEK(p.effective_date)) DAY) AS `from`,
                DATE_ADD(p.effective_date, INTERVAL(7 - DAYOFWEEK(p.effective_date)) DAY) AS `to`,
                AVG(p.price) AS average_price
            FROM 
                prices p
            JOIN 
                providers prov ON p.provider_id = prov.id
            GROUP BY 
                p.provider_id, 
                prov.name, 
                week,
                `from`,
                `to`
            ORDER BY 
                p.provider_id, week;
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop the trend_provider_price_by_month view
        DB::statement("DROP VIEW IF EXISTS trend_provider_price_by_month");

        // Drop the trend_provider_price_by_week view
        DB::statement("DROP VIEW IF EXISTS trend_provider_price_by_week");
    }
};

