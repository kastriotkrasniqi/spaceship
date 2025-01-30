<?php

namespace App\Console\Commands;

use App\Jobs\ProcessTradeRoute;
use App\Models\TradeAgreement;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ProcessTradeAgreements extends Command
{
    protected $signature = 'trade:process-agreements';
    protected $description = 'Process due trade agreements';

    public function handle()
    {
        $dueAgreements = TradeAgreement::where('next_delivery', '<=', Carbon::today())->get();

        foreach ($dueAgreements as $agreement) {
            ProcessTradeRoute::dispatch($agreement);
        }

        $this->info('Trade agreements have been queued for processing');
    }
}
