<?php

namespace App\Console\Commands;

use App\Services\Fcm;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendDailyNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-daily-notification';

    protected $description = 'Send daily notifications to users';

    protected Fcm $fcm;

    /**
     * The console command description.
     *
     * @var string
     */
    public function __construct(Fcm $fcm)
    {
        parent::__construct();
        $this->fcm = $fcm;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = today()->toDateString(); // Only string "YYYY-MM-DD"

        $reminders = DB::table('master_tender')
            ->where('remainder_date', $today)
            ->get();

        foreach ($reminders as $rem) {

            $formattedDate = \Carbon\Carbon::parse($today)->format('d-m-Y');

            $title = 'Tender Active';
            $body = "Tender - {$rem->project_name} Active #{$rem->tender_no} on {$formattedDate}.";

            // Get client FCM token
            $client = DB::table('master_clients')
                ->where('id', $rem->mc_id)
                ->select('fcm_token')
                ->first();

            // Save notification in DB
            DB::table('notifications')->insert([
                'mc_id' => $rem->mc_id,
                'title' => $title,
                'message' => $body,
                'type' => 'tender_active',
                'related_id' => $rem->id,
                'status' => 'unread',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($client && ! empty($client->fcm_token)) {

                try {

                    $this->fcm->send_notify($client->fcm_token, [
                        'title' => $title,
                        'body' => $body,
                    ]);

                    info("Daily notification sent for tender ID {$rem->id}");

                } catch (\Exception $e) {
                    info('Reminder Error: '.$e->getMessage());
                }
            }
        }

        $emd_reminders = DB::table('emd_remainder')
            ->where('rem_gen_date', $today)
            ->where('status', 'pending')
            ->get();

        // info('EMD Reminders: '.($emd_reminders));

        foreach ($emd_reminders as $rem) {
            $formattedDate = \Carbon\Carbon::parse($today)->format('d-m-Y');

            $title = 'EMD Refund Delay Alert';
            $body = "EMD refund delay for Tender ID #{$rem->t_id} is still pending beyond 30 days.Please follow up with the concerned authorities.";

            // Get client FCM token
            $client = DB::table('master_clients')
                ->where('id', $rem->mc_id)
                ->select('fcm_token')
                ->first();

            // Save notification in DB
            DB::table('notifications')->insert([
                'mc_id' => $rem->mc_id,
                'title' => $title,
                'message' => $body,
                'type' => 'emd_refund_alert',
                'related_id' => $rem->t_id,
                'status' => 'unread',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($client && ! empty($client->fcm_token)) {

                try {

                    $this->fcm->send_notify($client->fcm_token, [
                        'title' => $title,
                        'body' => $body,
                    ]);

                    info("EMD refund alert notification sent for reminder ID {$rem->id}");

                } catch (\Exception $e) {
                    info('EMD Refund Alert Error: '.$e->getMessage());
                }
            }
        }
    }
}

/* php artisan schedule:run

* * * * * cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1

 Schedule::command('app:send-daily-notification')->everyMinute();
 change to ->dailyAt('00:00')
 */
