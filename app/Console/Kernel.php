<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use App\Http\Controllers\Message;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\User;
use DB;

class Kernel extends ConsoleKernel {

    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];
    public $emails;

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     */
    protected function schedule(Schedule $schedule) {

        $schedule->call(function () {
            $this->sendSMS(); 
        })->everyMinute();

        $schedule->command('inspire')
                ->hourly();
    }

    // ->everyMinute();	Run the task every minute
    // ->everyFiveMinutes();	Run the task every five minutes
    // ->everyTenMinutes();	Run the task every ten minutes
    // ->everyFifteenMinutes();	Run the task every fifteen minutes
    // ->everyThirtyMinutes();	Run the task every thirty minutes
    // ->hourly();	Run the task every hour

    function sendSMS() {
        $messages = Message::where('status', 0)->limit(30)->get();
        if (count($messages) > 0) { 
            foreach($messages as $message){ 
                send_sms(str_replace('+', '', validate_phone_number(trim($message->phone))[1]), $message->body);
                //update message status
                Message::where('id', $message->id)->update(['statu' => 1, 'return_code' => 'Message Sent Successfuly']);
                DB::table('settings')->update(['statu' => 1, 'return_code' => 'Message Sent Successfuly']);
            }
        }else{
            return true;
        }
    }

    public function sendSchedulatedSms() {
        
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands() {
        require base_path('routes/console.php');
    }

}